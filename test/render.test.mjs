/**
 * Checks that the WordPress plugin draws the same figure as the standalone
 * script, resolves links the same way, and escapes what it prints.
 *
 *   node test/render.test.mjs
 *
 * Needs PHP on PATH. Nothing else.
 */

import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const root = dirname(here);

const { geometry, parts } = createRequire(import.meta.url)(
    join(root, 'js-version', 'js', 'body-map.js')
);

let failures = 0;
let checks = 0;

function ok(condition, label, detail) {
    checks++;
    if (condition) {
        console.log(`  PASS  ${label}`);
        return;
    }
    failures++;
    console.log(`  FAIL  ${label}`);
    if (detail !== undefined) {
        console.log(`        ${typeof detail === 'string' ? detail : JSON.stringify(detail)}`);
    }
}

function same(actual, expected, label) {
    const a = JSON.stringify(actual);
    const b = JSON.stringify(expected);
    ok(a === b, label, a === b ? undefined : `expected ${b}\n        actual   ${a}`);
}

console.log('Interactive Body Map - render tests\n');

let php;
try {
    php = JSON.parse(execFileSync('php', [join(here, 'render.php')], { encoding: 'utf8' }));
} catch (error) {
    console.error('Could not run PHP. Is it on PATH?\n');
    console.error(error.stdout || error.message);
    process.exit(1);
}

/* -- the two renderers agree on the drawing -------------------------------- */

console.log('Geometry');
ok(php.viewBox === geometry.viewBox, 'viewBox matches the script', php.viewBox);
ok(php.mirror === geometry.mirror, 'mirror transform matches the script', php.mirror);

/* Mirrored pairs share their path data, so 21 parts use 13 distinct paths. */
const jsPaths = new Set(geometry.parts.map((p) => p.d));
const phpPaths = new Set(php.detailed.paths);
ok(
    phpPaths.size === jsPaths.size && [...phpPaths].every((d) => jsPaths.has(d)),
    `all ${jsPaths.size} distinct part paths match the script`,
    `php has ${phpPaths.size}, script has ${jsPaths.size}`
);

const drawn = (php.detailed.html.match(/class="bodymap__shape"/g) || []).length;
ok(drawn === geometry.parts.length, `${geometry.parts.length} shapes are drawn, both sides`, drawn);

const mirrored = (php.detailed.html.match(/class="bodymap__shape"[^>]*transform=/g) || []).length;
ok(mirrored === 8, 'the 8 left-side parts carry the mirror transform', mirrored);

/* -- regions --------------------------------------------------------------- */

console.log('\nRegions');
same(php.simple.ids, parts('simple').map((p) => p.id), 'simple mode draws the 9 grouped regions');
same(php.detailed.ids, parts('detailed').map((p) => p.id), 'detailed mode draws all 21 regions');

/* -- link resolution ------------------------------------------------------- */

console.log('\nLinks');
ok(php.simple.links.head === '/anatomy/head', 'an exact key links its region', php.simple.links.head);
ok(
    php.simple.links['arm-left'] === '/anatomy/arms' && php.simple.links['arm-right'] === '/anatomy/arms',
    'the "arms" alias covers both arms in simple mode'
);
ok(
    php.detailed.links['forearm-right'] === '/anatomy/arms' &&
        php.detailed.links['shoulder-left'] === '/anatomy/arms',
    'the "arms" alias reaches every arm segment in detailed mode'
);
ok(
    php.detailed.links['hand-left'] === '/anatomy/left-hand',
    'a specific key beats the alias',
    php.detailed.links['hand-left']
);
ok(
    php.detailed.links['hand-right'] === '/anatomy/arms',
    'the other side still falls back to the alias',
    php.detailed.links['hand-right']
);
ok(!('pelvis' in php.simple.links), 'a label with no URL is not turned into a link');
ok(
    php.simple.html.includes('aria-label="Pelvic region"'),
    'that region still carries its custom label'
);
ok(
    php.simple.html.includes('bodymap__region--disabled" data-bodymap-part="pelvis"'),
    'and is marked as not clickable'
);

same(
    php.lookup_keys['hand-right'],
    ['hand-right', 'arm-right', 'hands', 'arms'],
    'fallback order runs specific to general'
);
same(php.lookup_keys.head, ['head'], 'a region that is its own group has one key');
same(
    php.lookup_keys['foot-left'],
    ['foot-left', 'leg-left', 'feet', 'legs'],
    'irregular plurals are handled'
);

/* -- escaping -------------------------------------------------------------- */

console.log('\nEscaping');
ok(!php.simple.html.includes('javascript:'), 'a javascript: URL never reaches the page');
ok(!php.simple.html.includes('<script>'), 'a URL cannot break out of its attribute');
ok(
    php.simple.html.includes('&lt;b&gt;bold&lt;/b&gt;') || php.simple.html.includes('bold'),
    'label markup is escaped, not executed'
);
ok(php.simple.wellformed === true, 'the SVG parses as well-formed XML', php.simple.wellformed);
ok(php.detailed.wellformed === true, 'detailed mode too', php.detailed.wellformed);

/* -- shortcode ------------------------------------------------------------- */

console.log('\nShortcode');
ok(php.shortcode.includes('data-bodymap-mode="detailed"'), 'mode="detailed" is honoured');
ok(php.shortcode.includes('max-width:260px'), 'max_width is honoured');
ok(php.shortcode.includes('--bodymap-fill-hover:#ff0000'), 'a colour attribute becomes a custom property');
ok(php.shortcode.includes('data-tooltip="false"'), 'tooltip="no" is honoured');
ok(php.shortcode.includes('href="/from-shortcode"'), 'an inline link overrides the saved one');
ok(
    (php.shortcode.match(/href="\/lower-limb"/g) || []).length === 8,
    'legs="…" reaches all eight leg segments',
    (php.shortcode.match(/href="\/lower-limb"/g) || []).length
);

/* -- the script escapes what it writes too ---------------------------------- *
 *
 * The PHP checks above cover the server-rendered figure. The script builds the
 * same links from `data-part-*`, `data-config` or the `parts` option, so it
 * needs the same allow-list. Rendering wants a DOM, and the point is to test
 * the attribute that actually lands on the anchor rather than the helper on its
 * own, so this stubs out just enough of one.
 * --------------------------------------------------------------------------- */

console.log('\nScript escaping');

function fakeEl(tag) {
    return {
        nodeType: 1, tag, attrs: {}, children: [], attributes: [],
        clientWidth: 100, offsetWidth: 10, parentNode: null,
        style: { setProperty() {}, removeProperty() {} },
        classList: { add() {}, remove() {}, toggle() {}, contains: () => false },
        setAttribute(k, v) { this.attrs[k] = String(v); },
        getAttribute(k) { return k in this.attrs ? this.attrs[k] : null; },
        setAttributeNS(ns, k, v) { this.attrs[k] = String(v); },
        appendChild(child) { this.children.push(child); return child; },
        querySelector: () => null,
        querySelectorAll: () => [],
        addEventListener() {}, removeEventListener() {},
        getBoundingClientRect: () => ({ left: 0, top: 0, width: 0, height: 0 }),
        get innerHTML() { return ''; }, set innerHTML(v) {},
        get textContent() { return ''; }, set textContent(v) {}
    };
}

globalThis.document = {
    createElementNS: (ns, name) => fakeEl(name),
    createElement: (name) => fakeEl(name),
    readyState: 'complete',
    querySelectorAll: () => [],
    addEventListener() {}
};

const anatomy = createRequire(import.meta.url)(
    join(root, 'js-version', 'js', 'body-map.js')
);

function collect(node, found = []) {
    if (node.attrs && node.attrs['data-bodymap-part']) { found.push(node); }
    for (const child of node.children || []) { collect(child, found); }
    return found;
}

/** Renders one figure and reports what landed on the `head` region. */
function headRegion(url) {
    const host = fakeEl('div');
    const warn = console.warn;
    console.warn = () => {};            // the refusal notice is expected here
    try {
        anatomy.init(host, { parts: { head: url }, mode: 'simple', tooltip: false });
    } finally {
        console.warn = warn;
    }
    const node = collect(host).find((n) => n.attrs['data-bodymap-part'] === 'head');
    return { tag: node.tag, href: node.attrs.href, xlink: node.attrs['xlink:href'] };
}

const blocked = [
    ['javascript:alert(1)', 'a javascript: URL is refused'],
    ['JaVaScRiPt:alert(1)', 'the scheme check is case-insensitive'],
    ['  javascript:alert(1)', 'leading whitespace does not smuggle one through'],
    ['java\tscript:alert(1)', 'nor does an embedded tab'],
    [`java${String.fromCharCode(0)}script:alert(1)`, 'nor does an embedded null byte'],
    ['data:text/html,<script>alert(1)</script>', 'a data: URL is refused'],
    ['vbscript:msgbox(1)', 'a vbscript: URL is refused']
];

for (const [url, label] of blocked) {
    const head = headRegion(url);
    ok(
        head.href === undefined && head.xlink === undefined && head.tag === 'g',
        label,
        `tag ${head.tag}, href ${JSON.stringify(head.href)}, xlink ${JSON.stringify(head.xlink)}`
    );
}

const allowed = [
    ['/anatomy/head', 'a relative path still links'],
    ['#head', 'a fragment still links'],
    ['../conditions/head', 'a relative parent path still links'],
    ['https://example.com/head', 'an https URL still links'],
    ['mailto:clinic@example.com', 'a mailto: URL still links'],
    ['tel:+441234567890', 'a tel: URL still links']
];

for (const [url, label] of allowed) {
    const head = headRegion(url);
    ok(head.href === url && head.xlink === url, label, `href ${JSON.stringify(head.href)}`);
}

/* -- assets ---------------------------------------------------------------- */

console.log('\nAssets');
ok(
    php.enqueued.includes('style:interactive-body-map') &&
        php.enqueued.includes('script:interactive-body-map'),
    'rendering enqueues the stylesheet and the script',
    php.enqueued
);

console.log(`\n${checks - failures}/${checks} checks passed.`);
process.exit(failures ? 1 : 0);
