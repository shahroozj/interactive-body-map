/*!
 * Interactive Body Map - v1.0.0
 * Clickable human body diagram. No dependencies. Transparent background.
 * https://github.com/shahroozj/interactive-body-map
 * Released under the MIT licence.
 */
(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        root.BodyMap = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : (typeof self !== 'undefined' ? self : this), function () {
    'use strict';

    var SVG_NS = 'http://www.w3.org/2000/svg';
    var XLINK_NS = 'http://www.w3.org/1999/xlink';
    var VIEW_BOX = '30 0 340 1000';
    var MIRROR = 'matrix(-1,0,0,1,400,0)';
    var uid = 0;

    /* ------------------------------------------------------------------ *
     * Geometry
     *
     * The figure is drawn on an eight-head canon in a 400 x 1000 space:
     * crown at y=20, chin at 140, nipples 260, navel 400, crotch 505,
     * knee 740, soles 978. The centre line is x=200, so parts on the
     * subject's left reuse the right-hand path through a mirror transform.
     * That halves the payload and makes the two sides exact.
     *
     * Adjacent parts share their seam coordinates verbatim, so the figure
     * never shows a hairline gap at any scale.
     *
     * Sides are anatomical: "right" is the subject's right, so it is drawn
     * on the viewer's left. Keep this in sync with includes/class-bodymap-model.php
     * in the WordPress plugin.
     * ------------------------------------------------------------------ */

    var PARTS = [
        {
            id: 'head',
            label: 'Head',
            group: 'head',
            d: 'M200,20 C227,20 247,43 247,76 C247,92 244,104 240,113 C234,126 218,140 200,140 C182,140 166,126 160,113 C156,104 153,92 153,76 C153,43 173,20 200,20 Z'
        },
        {
            id: 'neck',
            label: 'Neck',
            group: 'neck',
            d: 'M177,127 C177,143 177,155 175,163 C174,167 173,169 172,171 L228,171 C227,169 226,167 225,163 C223,155 223,143 223,127 C215,137 208,140 200,140 C192,140 185,137 177,127 Z'
        },
        {
            id: 'chest',
            label: 'Chest',
            group: 'chest',
            d: 'M172,171 C160,176 149,189 142,206 C133,232 126,264 124,292 C123,312 122,334 123,352 C150,344 174,338 200,338 C226,338 250,344 277,352 C278,334 277,312 276,292 C274,264 267,232 258,206 C251,189 240,176 228,171 Z'
        },
        {
            id: 'abdomen',
            label: 'Abdomen',
            group: 'abdomen',
            d: 'M123,352 C126,368 129,384 130,400 C131,416 128,428 126,434 C150,442 175,446 200,446 C225,446 250,442 274,434 C272,428 269,416 270,400 C271,384 274,368 277,352 C250,344 226,338 200,338 C174,338 150,344 123,352 Z'
        },
        {
            id: 'pelvis',
            label: 'Pelvis',
            group: 'pelvis',
            d: 'M126,434 C118,450 111,466 110,488 C109,510 111,530 114,545 L176,545 C183,542 190,536 196,528 C198,532 202,532 204,528 C210,536 217,542 224,545 L286,545 C289,530 291,510 290,488 C289,466 282,450 274,434 C250,442 225,446 200,446 C175,446 150,442 126,434 Z'
        },

        {
            id: 'shoulder-right',
            label: 'Right shoulder',
            group: 'arm-right',
            d: 'M142,206 C122,208 105,215 95,232 C85,250 77,272 77,296 C77,306 77,312 78,320 L118,322 C119,310 121,300 124,292 C127,262 133,232 142,206 Z'
        },
        {
            id: 'upper-arm-right',
            label: 'Right upper arm',
            group: 'arm-right',
            d: 'M78,320 L118,322 C118,346 118,368 117,386 C117,396 117,402 117,408 L81,410 C80,400 79,390 79,378 C78,358 78,340 78,320 Z'
        },
        {
            id: 'forearm-right',
            label: 'Right forearm',
            group: 'arm-right',
            d: 'M81,410 L117,408 C117,432 116,456 114,478 C113,496 113,508 113,520 L87,522 C85,508 83,494 82,476 C79,452 77,431 81,410 Z'
        },
        {
            id: 'hand-right',
            label: 'Right hand',
            group: 'arm-right',
            d: 'M87,522 L113,520 C114,536 115,550 114,564 C113,578 108,591 100,596 C93,600 86,597 83,590 C80,580 81,565 84,552 C85,541 86,530 87,522 Z'
        },

        {
            id: 'thigh-right',
            label: 'Right thigh',
            group: 'leg-right',
            d: 'M114,545 C118,580 120,620 122,658 C124,688 126,704 128,714 C145,720 163,720 180,714 C180,694 181,672 183,648 C186,612 191,570 196,528 C190,536 183,542 176,545 Z'
        },
        {
            id: 'knee-right',
            label: 'Right knee',
            group: 'leg-right',
            d: 'M128,714 C145,720 163,720 180,714 C180,732 179,748 178,758 C163,763 145,763 130,758 C129,744 128,728 128,714 Z'
        },
        {
            id: 'lower-leg-right',
            label: 'Right lower leg',
            group: 'leg-right',
            d: 'M130,758 C145,763 163,763 178,758 C179,782 177,808 174,834 C171,862 170,900 172,932 L146,932 C146,900 141,862 135,834 C129,808 126,782 130,758 Z'
        },
        {
            id: 'foot-right',
            label: 'Right foot',
            group: 'leg-right',
            d: 'M146,932 L172,932 C173,945 175,955 177,963 C180,971 177,977 169,977 L133,977 C125,977 122,971 126,963 C134,951 142,942 146,932 Z'
        }
    ];

    // The subject's left side: identical paths, mirrored.
    var LEFT_LABELS = {
        'shoulder-right': 'Left shoulder',
        'upper-arm-right': 'Left upper arm',
        'forearm-right': 'Left forearm',
        'hand-right': 'Left hand',
        'thigh-right': 'Left thigh',
        'knee-right': 'Left knee',
        'lower-leg-right': 'Left lower leg',
        'foot-right': 'Left foot'
    };

    (function buildLeftSide() {
        var mirrored = [];
        for (var i = 0; i < PARTS.length; i++) {
            var p = PARTS[i];
            if (p.id.slice(-6) !== '-right') { continue; }
            mirrored.push({
                id: p.id.slice(0, -6) + '-left',
                label: LEFT_LABELS[p.id],
                group: p.group.slice(0, -6) + '-left',
                d: p.d,
                mirror: true
            });
        }
        PARTS = PARTS.concat(mirrored);
    }());

    /* Decorative contours. Never interactive, never hit-tested.
     * The figure is deliberately faceless: it has to read as any visitor,
     * so the head carries only a hairline and ears. */
    var DETAILS = [
        'M157,84 C158,46 177,26 200,26 C223,26 242,46 243,84',            // hairline
        'M200,188 L200,268',                                              // sternum
        'M200,338 L200,424',                                              // linea alba
        'M176,368 C188,373 212,373 224,368',                              // upper ab crease
        'M178,398 C189,403 211,403 222,398',                              // lower ab crease
        'M196,428 C200,425 204,429 203,434 C201,440 196,439 195,434 Z'    // navel
    ];

    // Contours that exist on both sides.
    var DETAILS_PAIRED = [
        'M154,82 C147,81 145,92 148,101 C150,106 153,107 156,104',        // ear
        'M197,182 C187,191 172,195 154,192',                              // clavicle
        'M154,198 C161,230 176,250 198,252',                              // pectoral
        'M126,238 C113,240 102,250 96,264',                               // deltoid
        'M100,340 C104,362 106,382 106,402',                              // biceps
        'M142,486 C156,504 174,517 194,524',                              // inguinal crease
        'M84,550 C92,545 105,545 113,550',                                // knuckles
        'M152,782 C155,822 157,872 157,918',                              // shin
        'M133,964 C143,960 159,959 172,961'                               // toes
    ];

    var GROUP_LABELS = {
        'head': 'Head',
        'neck': 'Neck',
        'chest': 'Chest',
        'abdomen': 'Abdomen',
        'pelvis': 'Pelvis',
        'arm-right': 'Right arm',
        'arm-left': 'Left arm',
        'leg-right': 'Right leg',
        'leg-left': 'Left leg'
    };

    // Order regions appear in the DOM, which is also the tab order.
    var GROUP_ORDER = ['head', 'neck', 'chest', 'abdomen', 'pelvis',
        'arm-right', 'arm-left', 'leg-right', 'leg-left'];

    /* Fallback keys, tried in order, so `{ arms: '/arms' }` can cover both
     * arms and every segment inside them without listing 8 ids. */
    var PLURALS = {
        'arm': 'arms', 'leg': 'legs', 'hand': 'hands', 'foot': 'feet',
        'knee': 'knees', 'thigh': 'thighs', 'shoulder': 'shoulders',
        'forearm': 'forearms', 'upper-arm': 'upper-arms',
        'lower-leg': 'lower-legs'
    };

    function aliasesFor(id, group) {
        var keys = [id];
        if (group && group !== id) { keys.push(group); }
        for (var i = 0, n = keys.length; i < n; i++) {
            var k = keys[i];
            var m = /^(.+)-(left|right)$/.exec(k);
            if (m && PLURALS[m[1]]) { keys.push(PLURALS[m[1]]); }
        }
        return keys;
    }

    /* ------------------------------------------------------------------ *
     * Helpers
     * ------------------------------------------------------------------ */

    var DEFAULTS = {
        mode: 'simple',            // 'simple' (9 regions) | 'detailed' (21)
        parts: {},
        target: '',                // default link target, e.g. '_blank'
        rel: '',
        tooltip: true,
        tooltipTemplate: null,     // function (part) -> string
        maxWidth: '',
        title: 'Interactive body map',
        description: '',
        theme: null,               // { fill, hover, active, stroke, detail, ... }
        selectable: false,         // keep the last clicked region highlighted
        showDisabled: true,        // draw regions that have no link
        onSelect: null,            // (detail, event) -> false cancels navigation
        onHover: null
    };

    var THEME_VARS = {
        fill: '--bodymap-fill',
        hover: '--bodymap-fill-hover',
        active: '--bodymap-fill-active',
        disabled: '--bodymap-fill-disabled',
        stroke: '--bodymap-stroke',
        strokeWidth: '--bodymap-stroke-width',
        detail: '--bodymap-detail',
        tooltipBg: '--bodymap-tooltip-bg',
        tooltipColor: '--bodymap-tooltip-color',
        focus: '--bodymap-focus'
    };

    function svgEl(name, attrs) {
        var el = document.createElementNS(SVG_NS, name);
        for (var k in attrs) {
            if (Object.prototype.hasOwnProperty.call(attrs, k) && attrs[k] != null) {
                el.setAttribute(k, attrs[k]);
            }
        }
        return el;
    }

    function extend(target) {
        for (var i = 1; i < arguments.length; i++) {
            var src = arguments[i];
            if (!src) { continue; }
            for (var k in src) {
                if (Object.prototype.hasOwnProperty.call(src, k)) { target[k] = src[k]; }
            }
        }
        return target;
    }

    function camelToDash(s) {
        return s.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();
    }

    function isTruthyAttr(v) {
        return v !== 'false' && v !== '0' && v !== 'no';
    }

    /** Normalises `'url'` / `{ url: … }` / `false` into a config object. */
    function normalisePart(value) {
        if (value == null || value === false) { return { disabled: true }; }
        if (typeof value === 'string') { return { url: value }; }
        return value;
    }

    var SAFE_SCHEMES = { 'http:': 1, 'https:': 1, 'mailto:': 1, 'tel:': 1 };

    /**
     * Keeps scripting URLs out of the links this script writes.
     *
     * The same allow-list the WordPress plugin applies in PHP, so a link the
     * server would refuse is refused here too. A URL with no scheme at all -
     * `/services/knee`, `#head`, `../foo` - is relative and passes untouched.
     * A rejected URL becomes an empty string, which leaves the region drawn but
     * not clickable rather than dropping it from the figure.
     */
    function safeUrl(value) {
        var url = value == null ? '' : String(value);

        /* Browsers discard control characters and whitespace before they read
         * the scheme, so ` javascript:` and `java\tscript:` both run. Strip the
         * same set first or the test reads a scheme the browser never sees. */
        var probe = url.replace(/[\s\u0000-\u001f]/g, '');
        var scheme = /^[a-z][a-z0-9+.\-]*:/i.exec(probe);

        if (!scheme) { return url; }
        if (SAFE_SCHEMES[scheme[0].toLowerCase()]) { return url; }

        if (typeof console !== 'undefined' && console.warn) {
            console.warn('[BodyMap] refused a link with an unsafe scheme: ' + url);
        }
        return '';
    }

    /* ------------------------------------------------------------------ *
     * Instance
     * ------------------------------------------------------------------ */

    function Diagram(el, options) {
        this.el = el;
        this.options = extend({}, DEFAULTS, options || {});
        this.id = 'bodymap-' + (++uid);
        this.regions = [];
        this.selected = null;
        this._handlers = [];

        /* Markup that is already on the page — the WordPress plugin prints the
         * whole figure server-side — is adopted rather than thrown away. The
         * links are then real HTML from the first byte: crawlable, and visible
         * before this script has even loaded. */
        if (el.querySelector('svg.bodymap__svg')) {
            this.adopt();
        } else {
            this.render();
        }
    }

    Diagram.prototype._config = function (id, group) {
        var parts = this.options.parts || {};
        var keys = aliasesFor(id, group);
        for (var i = 0; i < keys.length; i++) {
            if (Object.prototype.hasOwnProperty.call(parts, keys[i])) {
                return normalisePart(parts[keys[i]]);
            }
        }
        return {};
    };

    /** The regions this instance draws, given the current mode. */
    Diagram.prototype._regionModel = function () {
        var detailed = this.options.mode === 'detailed';
        var out = [];
        var i;

        if (detailed) {
            for (i = 0; i < GROUP_ORDER.length; i++) {
                var g = GROUP_ORDER[i];
                for (var j = 0; j < PARTS.length; j++) {
                    if (PARTS[j].group !== g) { continue; }
                    var p = PARTS[j];
                    out.push({ id: p.id, label: p.label, group: p.group, shapes: [p] });
                }
            }
            return out;
        }

        for (i = 0; i < GROUP_ORDER.length; i++) {
            var gid = GROUP_ORDER[i];
            var shapes = [];
            for (var k = 0; k < PARTS.length; k++) {
                if (PARTS[k].group === gid) { shapes.push(PARTS[k]); }
            }
            out.push({ id: gid, label: GROUP_LABELS[gid], group: gid, shapes: shapes });
        }
        return out;
    };

    Diagram.prototype.render = function () {
        var o = this.options;
        var self = this;

        this.destroy(true);

        this.el.classList.add('bodymap');
        this.el.classList.toggle('bodymap--selectable', !!o.selectable);
        this.el.setAttribute('data-bodymap-mode', o.mode);
        if (o.maxWidth) { this.el.style.maxWidth = o.maxWidth; }

        if (o.theme) {
            for (var key in THEME_VARS) {
                if (o.theme[key]) { this.el.style.setProperty(THEME_VARS[key], o.theme[key]); }
            }
        }

        var svg = svgEl('svg', {
            'class': 'bodymap__svg',
            viewBox: VIEW_BOX,
            xmlns: SVG_NS,
            role: 'group',
            'aria-labelledby': this.id + '-title',
            preserveAspectRatio: 'xMidYMid meet',
            focusable: 'false'
        });

        var title = svgEl('title', { id: this.id + '-title' });
        title.textContent = o.title;
        svg.appendChild(title);

        if (o.description) {
            var desc = svgEl('desc', { id: this.id + '-desc' });
            desc.textContent = o.description;
            svg.appendChild(desc);
            svg.setAttribute('aria-describedby', this.id + '-desc');
        }

        var shapeLayer = svgEl('g', { 'class': 'bodymap__regions' });
        svg.appendChild(shapeLayer);

        var model = this._regionModel();
        this.regions = [];

        for (var i = 0; i < model.length; i++) {
            var region = model[i];
            var cfg = this._config(region.id, region.group);
            var url = cfg.disabled ? '' : safeUrl(cfg.url);
            var label = cfg.label || region.label;

            if (!url && !o.showDisabled) { continue; }

            var node;
            if (url) {
                node = svgEl('a', { 'class': 'bodymap__region', href: url });
                // Legacy attribute for older Safari / SVG 1.1 renderers.
                node.setAttributeNS(XLINK_NS, 'xlink:href', url);
                var tgt = cfg.target || o.target;
                if (tgt) { node.setAttribute('target', tgt); }
                var rel = cfg.rel || o.rel || (tgt === '_blank' ? 'noopener' : '');
                if (rel) { node.setAttribute('rel', rel); }
            } else {
                node = svgEl('g', { 'class': 'bodymap__region bodymap__region--disabled' });
            }

            node.setAttribute('data-bodymap-part', region.id);
            node.setAttribute('data-bodymap-group', region.group);
            node.setAttribute('aria-label', label);
            if (cfg.className) { node.setAttribute('class', node.getAttribute('class') + ' ' + cfg.className); }
            if (cfg.color) { node.style.setProperty('--bodymap-fill', cfg.color); }
            if (cfg.hoverColor) { node.style.setProperty('--bodymap-fill-hover', cfg.hoverColor); }

            for (var s = 0; s < region.shapes.length; s++) {
                var shape = region.shapes[s];
                node.appendChild(svgEl('path', {
                    'class': 'bodymap__shape',
                    d: shape.d,
                    transform: shape.mirror ? MIRROR : null
                }));
            }

            shapeLayer.appendChild(node);
            this.regions.push({ id: region.id, group: region.group, label: label, url: url, node: node, config: cfg });
        }

        // Contour layer sits above the regions but is transparent to input.
        var detailLayer = svgEl('g', { 'class': 'bodymap__details', 'aria-hidden': 'true' });
        var n;
        for (n = 0; n < DETAILS.length; n++) {
            detailLayer.appendChild(svgEl('path', { d: DETAILS[n] }));
        }
        for (n = 0; n < DETAILS_PAIRED.length; n++) {
            detailLayer.appendChild(svgEl('path', { d: DETAILS_PAIRED[n] }));
            detailLayer.appendChild(svgEl('path', { d: DETAILS_PAIRED[n], transform: MIRROR }));
        }
        svg.appendChild(detailLayer);

        this.el.appendChild(svg);
        this.svg = svg;

        return this._activate();
    };

    /** Wires up markup that was rendered elsewhere (server-side or by hand). */
    Diagram.prototype.adopt = function () {
        var o = this.options;

        this._unbind();
        this.el.classList.add('bodymap');
        this.el.classList.toggle('bodymap--selectable', !!o.selectable);
        if (o.maxWidth) { this.el.style.maxWidth = o.maxWidth; }
        if (o.mode === DEFAULTS.mode && this.el.getAttribute('data-bodymap-mode')) {
            o.mode = this.el.getAttribute('data-bodymap-mode');
        }

        this.svg = this.el.querySelector('svg.bodymap__svg');
        this.regions = [];

        var nodes = this.svg.querySelectorAll('[data-bodymap-part]');
        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];
            var id = node.getAttribute('data-bodymap-part');
            this.regions.push({
                id: id,
                group: node.getAttribute('data-bodymap-group') || id,
                label: node.getAttribute('aria-label') || id,
                url: node.getAttribute('href') || '',
                node: node,
                config: this._config(id, node.getAttribute('data-bodymap-group'))
            });
        }

        // A pre-rendered figure has no tooltip element of its own.
        this.tooltip = this.el.querySelector('.bodymap__tooltip');
        return this._activate();
    };

    Diagram.prototype._activate = function () {
        var self = this;

        if (this.options.tooltip && !this.tooltip) {
            this.tooltip = document.createElement('div');
            this.tooltip.className = 'bodymap__tooltip';
            this.tooltip.setAttribute('role', 'status');
            this.tooltip.setAttribute('aria-live', 'polite');
            this.el.appendChild(this.tooltip);
        } else if (!this.options.tooltip && this.tooltip) {
            this.tooltip.parentNode.removeChild(this.tooltip);
            this.tooltip = null;
        }

        this._on(this.el, 'pointerover', function (e) { self._onEnter(e); });
        this._on(this.el, 'pointerout', function (e) { self._onLeave(e); });
        this._on(this.el, 'pointermove', function (e) { self._onMove(e); });
        this._on(this.el, 'focusin', function (e) { self._onEnter(e); });
        this._on(this.el, 'focusout', function (e) { self._onLeave(e); });
        this._on(this.el, 'click', function (e) { self._onClick(e); });

        return this;
    };

    Diagram.prototype._on = function (node, type, fn) {
        node.addEventListener(type, fn, false);
        this._handlers.push([node, type, fn]);
    };

    Diagram.prototype._find = function (e) {
        var node = e.target;
        while (node && node !== this.el) {
            if (node.getAttribute && node.getAttribute('data-bodymap-part')) {
                for (var i = 0; i < this.regions.length; i++) {
                    if (this.regions[i].node === node) { return this.regions[i]; }
                }
                return null;
            }
            node = node.parentNode;
        }
        return null;
    };

    Diagram.prototype._onEnter = function (e) {
        var region = this._find(e);
        if (!region || region === this._hovered) { return; }
        this._hovered = region;
        region.node.classList.add('is-hover');
        this._showTooltip(region, e);
        if (this.options.onHover) { this.options.onHover(this._detail(region), e); }
    };

    Diagram.prototype._onLeave = function (e) {
        var region = this._find(e);
        if (!region) { return; }
        // pointerout fires when moving between paths inside the same region.
        if (e.relatedTarget && region.node.contains(e.relatedTarget)) { return; }
        region.node.classList.remove('is-hover');
        if (this._hovered === region) { this._hovered = null; }
        this._hideTooltip();
    };

    Diagram.prototype._onMove = function (e) {
        if (this._hovered && this.tooltip && e.pointerType !== 'touch') {
            this._positionTooltip(e);
        }
    };

    Diagram.prototype._onClick = function (e) {
        var region = this._find(e);
        if (!region) { return; }

        if (this.options.selectable) { this.select(region.id); }

        if (this.options.onSelect) {
            var result = this.options.onSelect(this._detail(region), e);
            if (result === false) { e.preventDefault(); return; }
        }

        if (!region.url) { e.preventDefault(); }
    };

    Diagram.prototype._detail = function (region) {
        return {
            id: region.id,
            group: region.group,
            label: region.label,
            url: region.url,
            element: region.node,
            instance: this
        };
    };

    Diagram.prototype._showTooltip = function (region, e) {
        if (!this.tooltip) { return; }
        var tpl = this.options.tooltipTemplate;
        var content = tpl ? tpl(this._detail(region)) : region.label;
        if (!content) { this._hideTooltip(); return; }

        if (tpl) { this.tooltip.innerHTML = content; }
        else { this.tooltip.textContent = content; }

        this.tooltip.classList.add('is-visible');

        if (e && e.clientX != null && e.pointerType !== 'touch') {
            this._positionTooltip(e);
        } else {
            this._anchorTooltip(region);
        }
    };

    Diagram.prototype._positionTooltip = function (e) {
        var box = this.el.getBoundingClientRect();
        this._placeTooltip(e.clientX - box.left, e.clientY - box.top - 14);
    };

    /** Used for keyboard focus and touch, where there is no useful cursor. */
    Diagram.prototype._anchorTooltip = function (region) {
        var box = this.el.getBoundingClientRect();
        var shape = region.node.getBoundingClientRect();
        this._placeTooltip(
            shape.left + shape.width / 2 - box.left,
            shape.top - box.top - 8
        );
    };

    Diagram.prototype._placeTooltip = function (x, y) {
        var w = this.el.clientWidth;
        var half = this.tooltip.offsetWidth / 2;
        this.tooltip.style.left = Math.max(half + 2, Math.min(w - half - 2, x)) + 'px';
        this.tooltip.style.top = Math.max(0, y) + 'px';
    };

    Diagram.prototype._hideTooltip = function () {
        if (this.tooltip) { this.tooltip.classList.remove('is-visible'); }
    };

    /* ---------------------------- public API --------------------------- */

    /** Highlights a region and keeps it highlighted. Pass null to clear. */
    Diagram.prototype.select = function (id) {
        for (var i = 0; i < this.regions.length; i++) {
            var match = this.regions[i].id === id;
            this.regions[i].node.classList.toggle('is-selected', match);
            if (match) { this.selected = this.regions[i].id; }
        }
        if (id == null) { this.selected = null; }
        return this;
    };

    Diagram.prototype.clear = function () { return this.select(null); };

    /** Merges new links in and re-renders. */
    Diagram.prototype.setParts = function (parts) {
        this.options.parts = extend({}, this.options.parts, parts);
        return this.render();
    };

    Diagram.prototype.setOptions = function (options) {
        this.options = extend({}, this.options, options);
        return this.render();
    };

    Diagram.prototype._unbind = function () {
        for (var i = 0; i < this._handlers.length; i++) {
            this._handlers[i][0].removeEventListener(this._handlers[i][1], this._handlers[i][2], false);
        }
        this._handlers = [];
        this.regions = [];
        this._hovered = null;
    };

    Diagram.prototype.destroy = function (keepInstance) {
        this._unbind();
        this.el.innerHTML = '';
        this.tooltip = null;
        this.svg = null;
        if (!keepInstance) {
            this.el.classList.remove('bodymap');
            delete this.el.__bodyMapInstance;
        }
        return this;
    };

    /* ------------------------------------------------------------------ *
     * Declarative configuration
     * ------------------------------------------------------------------ */

    var ATTR_OPTIONS = {
        'data-mode': ['mode', 'string'],
        'data-target': ['target', 'string'],
        'data-rel': ['rel', 'string'],
        'data-title': ['title', 'string'],
        'data-description': ['description', 'string'],
        'data-max-width': ['maxWidth', 'string'],
        'data-tooltip': ['tooltip', 'bool'],
        'data-selectable': ['selectable', 'bool'],
        'data-show-disabled': ['showDisabled', 'bool']
    };

    var THEME_ATTRS = ['fill', 'hover', 'active', 'disabled', 'stroke',
        'strokeWidth', 'detail', 'tooltipBg', 'tooltipColor', 'focus'];

    function optionsFromElement(el) {
        var opts = { parts: {} };
        var labels = {};
        var theme = {};
        var i, attr, key, def;

        var raw = el.getAttribute('data-config');
        if (raw) {
            try { extend(opts, JSON.parse(raw)); }
            catch (err) {
                if (window.console) { console.warn('[BodyMap] invalid data-config JSON', err); }
            }
        }
        if (!opts.parts) { opts.parts = {}; }

        for (i = 0; i < el.attributes.length; i++) {
            attr = el.attributes[i];
            def = ATTR_OPTIONS[attr.name];
            if (def) {
                opts[def[0]] = def[1] === 'bool' ? isTruthyAttr(attr.value) : attr.value;
                continue;
            }
            if (attr.name.indexOf('data-part-') === 0) {
                key = attr.name.slice(10);
                opts.parts[key] = extend(normalisePart(opts.parts[key]), { url: attr.value });
                continue;
            }
            if (attr.name.indexOf('data-label-') === 0) {
                labels[attr.name.slice(11)] = attr.value;
            }
        }

        for (i = 0; i < THEME_ATTRS.length; i++) {
            var v = el.getAttribute('data-' + camelToDash(THEME_ATTRS[i]) + '-color');
            if (!v) { v = el.getAttribute('data-' + camelToDash(THEME_ATTRS[i])); }
            if (v) { theme[THEME_ATTRS[i]] = v; }
        }
        if (Object.keys(theme).length) { opts.theme = extend(theme, opts.theme); }

        for (key in labels) {
            if (Object.prototype.hasOwnProperty.call(labels, key)) {
                opts.parts[key] = extend(normalisePart(opts.parts[key]), { label: labels[key] });
            }
        }

        return opts;
    }

    /* ------------------------------------------------------------------ *
     * Public entry points
     * ------------------------------------------------------------------ */

    function resolve(target) {
        if (!target) { return []; }
        if (typeof target === 'string') {
            return Array.prototype.slice.call(document.querySelectorAll(target));
        }
        if (target.nodeType === 1) { return [target]; }
        return Array.prototype.slice.call(target);
    }

    var API = {
        version: '1.0.0',

        /**
         * Creates a diagram inside each matched element.
         * Returns a single instance for one element, an array for many.
         */
        init: function (target, options) {
            var els = resolve(target);
            var made = [];
            for (var i = 0; i < els.length; i++) {
                var el = els[i];
                var fromMarkup = optionsFromElement(el);
                var merged = extend({}, fromMarkup, options || {});
                // Attribute links and scripted links merge rather than replace.
                merged.parts = extend({}, fromMarkup.parts, (options && options.parts) || {});
                if (el.__bodyMapInstance) { el.__bodyMapInstance.destroy(); }
                el.__bodyMapInstance = new Diagram(el, merged);
                made.push(el.__bodyMapInstance);
            }
            return made.length === 1 ? made[0] : made;
        },

        /** Finds the instance attached to an element, if any. */
        get: function (target) {
            var el = resolve(target)[0];
            return el ? el.__bodyMapInstance || null : null;
        },

        /** Boots every `[data-body-map]` element not already booted. */
        autoInit: function (scope) {
            var els = (scope || document).querySelectorAll('[data-body-map]');
            var made = [];
            for (var i = 0; i < els.length; i++) {
                if (!els[i].__bodyMapInstance) { made.push(API.init(els[i])); }
            }
            return made;
        },

        /** Metadata for every region, for building menus or admin screens. */
        parts: function (mode) {
            var out = [];
            var i;
            if (mode === 'detailed') {
                for (i = 0; i < GROUP_ORDER.length; i++) {
                    for (var j = 0; j < PARTS.length; j++) {
                        if (PARTS[j].group === GROUP_ORDER[i]) {
                            out.push({ id: PARTS[j].id, label: PARTS[j].label, group: PARTS[j].group });
                        }
                    }
                }
                return out;
            }
            for (i = 0; i < GROUP_ORDER.length; i++) {
                out.push({ id: GROUP_ORDER[i], label: GROUP_LABELS[GROUP_ORDER[i]], group: GROUP_ORDER[i] });
            }
            return out;
        },

        Diagram: Diagram,

        /* The raw model. The build script reads this to generate the
         * WordPress plugin's PHP geometry, so the server-rendered figure and
         * this one are the same drawing by construction. */
        geometry: {
            viewBox: VIEW_BOX,
            mirror: MIRROR,
            parts: PARTS,
            details: DETAILS,
            detailsPaired: DETAILS_PAIRED,
            groupLabels: GROUP_LABELS,
            groupOrder: GROUP_ORDER,
            plurals: PLURALS
        }
    };

    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { API.autoInit(); });
        } else {
            API.autoInit();
        }
    }

    return API;
}));
