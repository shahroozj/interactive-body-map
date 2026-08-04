# Installation

Two distributions, same diagram. Install whichever suits the site.

- [WordPress plugin](#wordpress-plugin)
- [Standard JavaScript](#standard-javascript)

---

## WordPress plugin

### Install

1. **Plugins → Add New → Upload Plugin**.
2. Choose `interactive-body-map-wordpress-1.0.0.zip`.
3. **Install Now**, then **Activate**.

Requires WordPress 5.8 or newer and PHP 7.0 or newer. It works with both the
block editor and the classic editor, and with any theme.

To install by hand instead, unzip the folder into `wp-content/plugins/` so you
end up with `wp-content/plugins/interactive-body-map/`, then activate it
from the Plugins screen.

### Set the links

Go to **Settings → Body Map**.

Each row is one part of the body. Fill in:

- **Link** — where that part goes. A path such as `/knee-pain` is fine; so is a
  full URL, `mailto:` or `tel:`.
- **Tooltip** — an optional label. Leave it empty to use the region's own name.
- **New tab** — tick to open that one link in a new tab.

The **whole limb** row at the top of each arm and leg links every segment of
that limb at once. Fill that in first; add specific rows only where a segment
needs to go somewhere else. See [the region reference](PARTS.md) for the exact
fallback order.

### Put it on a page

**Block editor** — add the **Body Map** block. Its sidebar overrides the
layout and behaviour for that one block; leave a control on *Use the saved
setting* to follow your global configuration.

**Shortcode** — paste `[body_map]` into any post, page, widget or
template. Every setting can be overridden per shortcode:

```
[body_map mode="detailed" max_width="260px"]
[body_map hover="#0ea5e9" tooltip="no" show_list="yes"]
[body_map head="/head-injuries" chest="/chest-pain" arms="/upper-limb"]
```

**In a theme template**:

```php
<?php echo do_shortcode( '[body_map]' ); ?>
```

#### Shortcode attributes

| Attribute | Values | Description |
|---|---|---|
| `mode` | `simple`, `detailed` | 9 or 21 regions |
| `max_width` | CSS length | e.g. `340px`, `100%` |
| `align` | `left`, `center`, `right` | Horizontal placement |
| `target` | `_blank` | Open every link in a new tab |
| `title` | text | Accessible name of the figure |
| `class` | text | Extra CSS classes on the wrapper |
| `tooltip` | `yes`, `no` | Floating region label |
| `selectable` | `yes`, `no` | Keep the clicked region highlighted |
| `show_disabled` | `yes`, `no` | Draw regions that have no link |
| `show_list` | `yes`, `no` | Add a plain list of the same links below |
| `outline` | `yes`, `no` | Fill regions only on hover |
| `fill`, `hover`, `active`, `disabled`, `stroke`, `detail`, `tooltip_bg`, `tooltip_text` | hex colour | Colour overrides |
| any region ID or fallback key | URL | Overrides the saved link for that region |

### Styling from your theme

The colours on the settings screen cover most cases. For anything else,
override the CSS custom properties — they beat nothing and lose to nothing:

```css
.bodymap {
    --bodymap-fill: #cbd5e1;
    --bodymap-fill-hover: #0ea5e9;
    --bodymap-fill-active: #0369a1;
    --bodymap-stroke: #64748b;
    --bodymap-detail: #64748b;
}

/* one region */
.bodymap__region[data-bodymap-part="head"] { --bodymap-fill-hover: #f59e0b; }

/* one whole limb */
.bodymap__region[data-bodymap-group="leg-left"] { --bodymap-fill-hover: #f59e0b; }
```

Leave a colour empty in the settings screen and the plugin prints no inline
style for it, so your CSS is free to take over.

### Notes

- **Caching and minification plugins** are fine. The output is plain HTML and
  inline SVG with no inline scripts.
- **Multiple diagrams on one page** work; each shortcode or block is
  independent.
- **Deleting the plugin** removes its single option. Deactivating it leaves
  everything in place.
- **Translations** go in `languages/`. The template is
  `interactive-body-map.pot`.

---

## Standard JavaScript

### Install

Copy `css/` and `js/` from the zip into your site, then:

```html
<!-- in <head> -->
<link rel="stylesheet" href="/assets/css/body-map.min.css">

<!-- wherever the diagram belongs -->
<div data-body-map
     data-part-head="/conditions/head"
     data-part-neck="/conditions/neck"
     data-part-chest="/conditions/chest"
     data-part-abdomen="/conditions/abdomen"
     data-part-arms="/conditions/arms"
     data-part-legs="/conditions/legs"></div>

<!-- before </body> -->
<script src="/assets/js/body-map.min.js"></script>
```

Anything carrying `data-body-map` starts itself on page load. There is no
JavaScript to write and no build step.

The unminified `body-map.js` and `body-map.css` are in the zip too,
if you would rather read or edit the source.

### Configuring from JavaScript

For callbacks, per-region colours or links you build at runtime:

```html
<div id="body-map"></div>
<script src="/assets/js/body-map.min.js"></script>
<script>
BodyMap.init('#body-map', {
    mode: 'detailed',
    target: '_blank',
    parts: {
        head:  '/anatomy/head',
        chest: { url: '/anatomy/chest', label: 'Chest & ribs' },
        arms:  '/anatomy/arms',
        legs:  '/anatomy/legs',
        'hand-left': { url: '/anatomy/hands', color: '#f59e0b' },
        pelvis: false
    },
    theme: { fill: '#cbd5e1', hover: '#0ea5e9' },
    onSelect: function (part, event) {
        console.log(part.id, part.label, part.url);
        // return false to stay on the page
    }
});
</script>
```

The full option, method and styling reference is in `index.html`, which is also
a live demo — open it in a browser.

### Module builds

The file is UMD, so it also works as a module:

```js
import BodyMap from './body-map.js';
BodyMap.init('#body-map', { /* … */ });
```

```js
const BodyMap = require('./body-map.js');
```

Auto-initialisation still happens when a `document` exists, so a bundled build
picks up `data-body-map` elements exactly like a script tag does.

### Frameworks

Render an empty container, then initialise it once it is in the DOM, and
destroy it on teardown:

```js
// React
useEffect(() => {
    const chart = BodyMap.init(ref.current, options);
    return () => chart.destroy();
}, []);
```

```js
// Vue
onMounted(() => { instance = BodyMap.init(el.value, options); });
onUnmounted(() => instance.destroy());
```

### Content Security Policy

No inline styles or scripts are injected and nothing is fetched, so the default
`script-src 'self'; style-src 'self'` is enough. Per-region colours are set as
CSS custom properties on elements, which needs no `'unsafe-inline'`.

---

## Troubleshooting

**The diagram does not appear.** Check the browser console for a 404 on the CSS
or JS. In WordPress, confirm the plugin is activated.

**Nothing is clickable.** No links are set yet. Fill them in under
**Settings → Body Map**, or add `data-part-*` attributes.

**Left and right look swapped.** They are anatomical: the figure faces you, so
its right arm is on your left.

**It is far too tall.** A full-body figure is about four times taller than it
is wide. Set a smaller `max_width` — `260px` suits most sidebars.

**A region stays highlighted after tapping on a phone.** That is a browser
retaining focus after navigation. If it bothers you, turn off *Keep the clicked
region highlighted*.

**Colours from my theme are ignored.** A colour set in the settings screen is
printed as an inline style and wins. Clear it there and style it in CSS.
