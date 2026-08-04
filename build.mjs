/**
 * Build script.
 *
 *   node build.mjs
 *
 * Minifies the library, then copies both the readable and the minified files
 * into the WordPress plugin. The plugin never owns its own copy of the
 * component: js-version/ is the single source, so the two distributions can
 * never drift apart.
 */

import { transformSync } from 'esbuild';
import { mkdirSync, copyFileSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';
import { gzipSync } from 'node:zlib';

const root = dirname(fileURLToPath(import.meta.url));
const src = join(root, 'js-version');
const pluginRoot = join(root, 'wordpress-plugin', 'interactive-body-map');
const plugin = join(pluginRoot, 'assets');
const TEXT_DOMAIN = 'interactive-body-map';

function minify(from, to, loader) {
    const { code, warnings } = transformSync(readFileSync(from, 'utf8'), {
        loader,
        minify: true,
        legalComments: 'inline',
        target: loader === 'js' ? 'es2017' : undefined
    });

    for (const warning of warnings) {
        console.warn(`  ! ${warning.text}`);
    }

    writeFileSync(to, code);
}

function report(label, file) {
    const raw = readFileSync(file);
    const kb = (n) => (n / 1024).toFixed(1).padStart(5) + ' KB';
    console.log(`  ${label.padEnd(26)} ${kb(raw.length)}   ${kb(gzipSync(raw).length)} gzipped`);
}

const targets = [
    ['js/body-map.js', 'js/body-map.min.js', 'js'],
    ['css/body-map.css', 'css/body-map.min.css', 'css']
];

console.log('Building Interactive Body Map\n');

for (const [input, output, loader] of targets) {
    minify(join(src, input), join(src, output), loader);
    report(output, join(src, output));
}

/* ---------------------------------------------------------------------- *
 * Generate the plugin's PHP geometry from the JavaScript model.
 * ---------------------------------------------------------------------- */

const { geometry } = createRequire(import.meta.url)(join(src, 'js', 'body-map.js'));

const q = (s) => "'" + String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
const t = (s) => `__( ${q(s)}, ${q(TEXT_DOMAIN)} )`;

const phpParts = geometry.parts.map((p) => `\t\t\tarray(
\t\t\t\t'id'     => ${q(p.id)},
\t\t\t\t'label'  => ${t(p.label)},
\t\t\t\t'group'  => ${q(p.group)},
\t\t\t\t'mirror' => ${p.mirror ? 'true' : 'false'},
\t\t\t\t'd'      => ${q(p.d)},
\t\t\t),`).join('\n');

const phpList = (arr, indent = '\t\t\t') => arr.map((s) => `${indent}${q(s)},`).join('\n');
const phpMap = (obj, value = q) => Object.entries(obj)
    .map(([k, v]) => `\t\t\t${q(k)} => ${value(v)},`).join('\n');

writeFileSync(join(pluginRoot, 'includes', 'class-bodymap-geometry.php'), `<?php
/**
 * Figure geometry.
 *
 * GENERATED FILE - do not edit by hand.
 * Produced by build.mjs from js-version/js/body-map.js, which is the one
 * place the drawing is defined. Re-run \`node build.mjs\` after changing it.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * The paths that make up the figure, plus the naming tables that go with them.
 */
final class BodyMap_Geometry {

\t/** SVG viewBox for the whole figure. */
\tconst VIEW_BOX = ${q(geometry.viewBox)};

\t/** Transform applied to parts on the subject's left. */
\tconst MIRROR = ${q(geometry.mirror)};

\t/**
\t * Every drawable part, in drawing order.
\t *
\t * @return array<int, array<string, mixed>>
\t */
\tpublic static function parts() {
\t\treturn array(
${phpParts}
\t\t);
\t}

\t/**
\t * Decorative contours drawn once, on the centre line.
\t *
\t * @return string[]
\t */
\tpublic static function details() {
\t\treturn array(
${phpList(geometry.details)}
\t\t);
\t}

\t/**
\t * Decorative contours drawn twice, once mirrored.
\t *
\t * @return string[]
\t */
\tpublic static function details_paired() {
\t\treturn array(
${phpList(geometry.detailsPaired)}
\t\t);
\t}

\t/**
\t * Display name for each region group.
\t *
\t * @return array<string, string>
\t */
\tpublic static function group_labels() {
\t\treturn array(
${phpMap(geometry.groupLabels, t)}
\t\t);
\t}

\t/**
\t * Group order, which is also the tab order.
\t *
\t * @return string[]
\t */
\tpublic static function group_order() {
\t\treturn array(
${phpList(geometry.groupOrder)}
\t\t);
\t}

\t/**
\t * Side-less fallback keys, so one setting can cover both limbs.
\t *
\t * @return array<string, string>
\t */
\tpublic static function plurals() {
\t\treturn array(
${phpMap(geometry.plurals)}
\t\t);
\t}
}
`);

console.log(`\n  includes/class-bodymap-geometry.php  (${geometry.parts.length} parts)`);

console.log('\nSyncing into the WordPress plugin');

for (const [input, output] of targets) {
    for (const file of [input, output]) {
        const dest = join(plugin, file);
        mkdirSync(dirname(dest), { recursive: true });
        copyFileSync(join(src, file), dest);
        console.log(`  assets/${file}`);
    }
}

/* ---------------------------------------------------------------------- *
 * Translation template.
 *
 * Extracted from the source rather than maintained by hand, so a new string
 * can never be left untranslatable.
 * ---------------------------------------------------------------------- */

const CALLS = '__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e';
const STRING_RE = new RegExp(
    `(?:${CALLS})\\(\\s*(['"])((?:\\\\.|(?!\\1).)*)\\1\\s*,\\s*['"]${TEXT_DOMAIN}['"]`,
    'g'
);

function* walk(dir) {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
        const full = join(dir, entry.name);
        if (entry.isDirectory()) {
            yield* walk(full);
        } else if (/\.(php|js)$/.test(entry.name) && !/\.min\.js$/.test(entry.name)) {
            yield full;
        }
    }
}

const strings = new Map();

for (const file of walk(pluginRoot)) {
    const body = readFileSync(file, 'utf8');
    const lines = body.split('\n');

    for (const match of body.matchAll(STRING_RE)) {
        // Unescape the source string's own quoting.
        const text = match[2].replace(/\\(['"\\])/g, '$1');
        const line = lines.length - body.slice(match.index).split('\n').length + 1;
        const ref = `${relative(pluginRoot, file).replace(/\\/g, '/')}:${line}`;

        if (strings.has(text)) {
            strings.get(text).push(ref);
        } else {
            strings.set(text, [ref]);
        }
    }
}

const potBody = [...strings.entries()].map(([text, refs]) =>
    `${refs.map((r) => `#: ${r}`).join('\n')}\nmsgid "${text.replace(/(["\\])/g, '\\$1')}"\nmsgstr ""\n`
).join('\n');

mkdirSync(join(pluginRoot, 'languages'), { recursive: true });
writeFileSync(join(pluginRoot, 'languages', `${TEXT_DOMAIN}.pot`), `# Copyright (C) 2026 Shahrooz Jafari
# This file is distributed under the MIT licence, like the plugin itself.
msgid ""
msgstr ""
"Project-Id-Version: Interactive Body Map\\n"
"Report-Msgid-Bugs-To: https://github.com/shahroozj/interactive-body-map/issues\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"X-Domain: ${TEXT_DOMAIN}\\n"

${potBody}`);

console.log(`  languages/${TEXT_DOMAIN}.pot  (${strings.size} strings)`);

/* The plugin advertises its own version in one place; keep the readme's
 * "Stable tag" pointing at it so WordPress never shows a stale version. */
const mainFile = join(root, 'wordpress-plugin', 'interactive-body-map', 'interactive-body-map.php');
const version = /^\s*\*\s*Version:\s*(.+)$/m.exec(readFileSync(mainFile, 'utf8'))?.[1].trim();
const readmePath = join(root, 'wordpress-plugin', 'interactive-body-map', 'readme.txt');
const readme = readFileSync(readmePath, 'utf8');
const patched = readme.replace(/^Stable tag: .*$/m, `Stable tag: ${version}`);
if (patched !== readme) { writeFileSync(readmePath, patched); }

console.log(`\nDone. Plugin version ${version}.`);
