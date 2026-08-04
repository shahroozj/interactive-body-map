# Interactive Body Map — JavaScript version

A clickable human body diagram in plain HTML, CSS and JavaScript. No
dependencies, no build step.

## Files

| File | |
|---|---|
| `index.html` | Live demo and the full reference — **open this first** |
| `demo-simple.html` | The smallest possible integration |
| `css/body-map.min.css` | Stylesheet, 1.2 KB gzipped |
| `js/body-map.min.js` | Script, 5.7 KB gzipped |
| `css/body-map.css` | Readable stylesheet |
| `js/body-map.js` | Readable, commented script |

Ship the two `.min` files. The unminified pair is there to read and edit.

## Install

```html
<link rel="stylesheet" href="css/body-map.min.css">

<div data-body-map
     data-part-head="/conditions/head"
     data-part-chest="/conditions/chest"
     data-part-abdomen="/conditions/abdomen"
     data-part-arms="/conditions/arms"
     data-part-legs="/conditions/legs"></div>

<script src="js/body-map.min.js"></script>
```

Elements carrying `data-body-map` start themselves on page load.

## From JavaScript

```js
BodyMap.init('#body-map', {
    mode: 'detailed',
    parts: {
        head:  '/anatomy/head',
        arms:  '/anatomy/arms',
        legs:  '/anatomy/legs',
        chest: { url: '/anatomy/chest', label: 'Chest & ribs' },
        pelvis: false
    },
    theme: { hover: '#0ea5e9' },
    onSelect: function (part, event) {
        // return false to stay on the page
    }
});
```

## Options

| Option | Attribute | Default | |
|---|---|---|---|
| `mode` | `data-mode` | `"simple"` | `"simple"` (9 regions) or `"detailed"` (21) |
| `parts` | `data-part-*` | `{}` | Region ID → URL, or an object |
| `target` | `data-target` | `""` | Default link target |
| `rel` | `data-rel` | `""` | Default link rel |
| `tooltip` | `data-tooltip` | `true` | Floating region label |
| `tooltipTemplate` | — | `null` | `(part) => html` |
| `selectable` | `data-selectable` | `false` | Keep the clicked region highlighted |
| `showDisabled` | `data-show-disabled` | `true` | Draw regions with no link |
| `maxWidth` | `data-max-width` | `""` | Any CSS length |
| `title` | `data-title` | … | Accessible name |
| `description` | `data-description` | `""` | Longer accessible description |
| `theme` | `data-fill`, `data-hover`, `data-active`, `data-stroke`, `data-detail` | `null` | Colour overrides |
| `onSelect` | — | `null` | `(part, event)`; return `false` to cancel navigation |
| `onHover` | — | `null` | `(part, event)` |

A part value can be a URL string, `false` to draw it but not link it, or an
object: `{ url, label, target, rel, color, hoverColor, className }`.

There is also `data-config='{"parts":{…}}'` if you would rather pass JSON.

## Methods

| | |
|---|---|
| `BodyMap.init(target, options)` | Create. `target` is a selector, element or list |
| `BodyMap.get(target)` | The instance attached to an element |
| `BodyMap.autoInit(scope)` | Start any `[data-body-map]` not yet started |
| `BodyMap.parts(mode)` | Array of region metadata |
| `instance.setParts({…})` | Merge in new links and redraw |
| `instance.setOptions({…})` | Merge in new options and redraw |
| `instance.select(id)` / `.clear()` | Highlight a region |
| `instance.destroy()` | Remove everything and unbind |

## Styling

Every colour is a CSS custom property:

```css
.bodymap {
    --bodymap-fill: #cbd5e1;
    --bodymap-fill-hover: #0ea5e9;
    --bodymap-fill-active: #0369a1;
    --bodymap-fill-disabled: #e2e8f0;
    --bodymap-stroke: #64748b;
    --bodymap-stroke-width: 1.6;
    --bodymap-detail: #64748b;
    --bodymap-focus: #0ea5e9;
    --bodymap-tooltip-bg: #0f172a;
    --bodymap-tooltip-color: #fff;
    --bodymap-duration: 160ms;
    max-width: 340px;
}

.bodymap__region[data-bodymap-part="head"]     { --bodymap-fill-hover: #f59e0b; }
.bodymap__region[data-bodymap-group="leg-left"] { --bodymap-fill-hover: #f59e0b; }
```

Add `class="bodymap--outline"` for an outline style that fills only on hover.

The stylesheet answers `prefers-color-scheme: dark` and
`prefers-reduced-motion`. Add `data-bodymap-theme="light"` to opt one diagram out of
the dark palette.

## Region IDs

`head`, `neck`, `chest`, `abdomen`, `pelvis`, `arm-right`, `arm-left`,
`leg-right`, `leg-left` — plus, in detailed mode, `shoulder-*`, `upper-arm-*`,
`forearm-*`, `hand-*`, `thigh-*`, `knee-*`, `lower-leg-*` and `foot-*`.

Sides are anatomical: the figure faces you, so its right arm is on your left.

One key can cover a whole limb — `arms`, `legs`, `hands`, `feet`, `knees`,
`thighs`, `shoulders`, `forearms`, `upper-arms`, `lower-legs` — and a more
specific key always wins. See [`../docs/PARTS.md`](../docs/PARTS.md).

## Browser support

Every current browser, plus Safari 14+, and Chrome, Edge and Firefox from 2020
onwards. It uses SVG, CSS custom properties and pointer events, all of which
have been baseline for years. Internet Explorer is not supported.

## Pre-rendered markup

If the container already holds the figure's markup, the script adopts it
instead of rebuilding it — the links are then real HTML from the first byte,
which is what the WordPress plugin does. Anything you print with the same class
names is enhanced the same way.
