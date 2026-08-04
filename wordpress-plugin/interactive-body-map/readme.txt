=== Interactive Body Map ===
Contributors: shahroozj
Tags: anatomy, body, medical, interactive, svg
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.0
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

A clickable human body diagram. Link the head, arms, legs, chest, abdomen and
more to any page on your site.

== Description ==

Interactive Body Map adds a clickable diagram of the human body to any
post, page or widget. Each region - head, neck, chest, abdomen, pelvis, arms and
legs - can point at any URL you like, so a clinic can send visitors from the
knee to its knee treatment page, or a fitness site can link each muscle group to
its own workout.

The figure is scalable vector artwork on a transparent background. It has no
background of its own, so it sits directly on your page colour, photo or
gradient rather than in a white box.

= What you get =

* **Every region links anywhere.** Internal pages, external sites, `mailto:` or
  `tel:` links, and an optional "open in a new tab" per region.
* **Two levels of detail.** Simple mode gives you nine regions. Detailed mode
  splits the limbs into shoulder, upper arm, forearm and hand, and thigh, knee,
  lower leg and foot - twenty-one in all.
* **Real links.** Each region is a genuine `<a href>` printed in the page HTML
  by PHP. Search engines index them, middle-click opens them in a new tab, and
  the diagram is already usable before any JavaScript has run.
* **Responsive and resizable.** Vector artwork scales to any width without
  losing sharpness. Set one maximum width and it fits every screen.
* **Works on touch.** Hover effects are gated so a tap never leaves a region
  stuck in the hover colour. Tested on iPhone, iPad, Android phones and tablets.
* **Keyboard and screen reader friendly.** Tab moves between regions in
  anatomical order, Enter follows the link, and every region carries a label.
* **Small and fast.** One stylesheet and one script, around 11 KB together
  after compression. No images, no web fonts, no external requests, no jQuery
  on the front end.
* **Themeable.** Eight colours in the settings screen, or override the CSS
  custom properties from your theme for full control.
* **Blocks and shortcodes.** Use the "Body Map" block, or
  `[body_map]` anywhere shortcodes run.

= Licence =

Released under the MIT licence. Free to use, modify and redistribute, including
commercially, provided the copyright notice and licence text are kept.

== Installation ==

1. In WordPress, go to **Plugins > Add New > Upload Plugin** and choose the zip.
2. Activate it.
3. Go to **Settings > Body Map** and give each part of the body a link.
4. Add the **Body Map** block to a page, or paste `[body_map]` into
   any post, page, widget or template.

== Frequently Asked Questions ==

= Can I use more than one diagram on a page? =

Yes. Each shortcode or block renders independently, and the shortcode can
override any setting for that one instance.

= How do I point a single page's diagram somewhere else? =

Pass the links on the shortcode:

`[body_map head="/head-injuries" chest="/chest-pain" arms="/upper-limb"]`

Anything you do not pass falls back to the saved settings.

= Left and right look swapped. =

They are anatomical. The figure faces you, so its right arm is drawn on your
left, the same convention every medical diagram uses.

= Can I change the colours? =

Eight colours are on the settings screen. For finer control, override the CSS
custom properties in your theme:

`.bodymap { --bodymap-fill: #cbd5e1; --bodymap-fill-hover: #0ea5e9; }`

Or restyle a single region:

`.bodymap__region[data-bodymap-part="head"] { --bodymap-fill-hover: #f59e0b; }`

= Does it work with caching and minification plugins? =

Yes. The markup is plain HTML and inline SVG with no inline scripts, so page
caches store it as-is.

= Will it slow my site down? =

No. There are two small static files and no image requests at all. The figure
is drawn from vector paths in the page itself.

== Changelog ==

= 1.0.0 =
* First release.
