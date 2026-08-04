# Region reference

Every region has an ID. You use it as a `data-part-<id>` attribute, as a key in
the `parts` option, as a row on the WordPress settings screen, or as a
shortcode attribute.

## Sides are anatomical

The figure faces you, so **its right arm is drawn on your left**. This is the
convention every medical diagram uses. `arm-right` is the subject's right arm,
on the viewer's left.

## Simple mode — 9 regions

The default. Each arm and each leg is one clickable region.

| ID | Label |
|---|---|
| `head` | Head |
| `neck` | Neck |
| `chest` | Chest |
| `abdomen` | Abdomen |
| `pelvis` | Pelvis |
| `arm-right` | Right arm |
| `arm-left` | Left arm |
| `leg-right` | Right leg |
| `leg-left` | Left leg |

## Detailed mode — 21 regions

`head`, `neck`, `chest`, `abdomen` and `pelvis` are unchanged. The limbs split:

| ID | Label |
|---|---|
| `shoulder-right` / `shoulder-left` | Right / left shoulder |
| `upper-arm-right` / `upper-arm-left` | Right / left upper arm |
| `forearm-right` / `forearm-left` | Right / left forearm |
| `hand-right` / `hand-left` | Right / left hand |
| `thigh-right` / `thigh-left` | Right / left thigh |
| `knee-right` / `knee-left` | Right / left knee |
| `lower-leg-right` / `lower-leg-left` | Right / left lower leg |
| `foot-right` / `foot-left` | Right / left foot |

## Fallback keys

You rarely need to fill in all 21. A region looks for its link under several
keys, and takes the first one that has a URL:

1. its own ID — `hand-right`
2. its group — `arm-right`
3. its side-less plural — `hands`
4. the group's side-less plural — `arms`

So `arms: '/upper-limb'` links both arms and all eight arm segments at once,
while `hand-left: '/hand-therapy'` still overrides it for that one hand,
because a more specific key is checked first.

The side-less keys:

| Key | Covers |
|---|---|
| `arms` | both arms, and every segment of them |
| `legs` | both legs, and every segment of them |
| `shoulders` | both shoulders |
| `upper-arms` | both upper arms |
| `forearms` | both forearms |
| `hands` | both hands |
| `thighs` | both thighs |
| `knees` | both knees |
| `lower-legs` | both lower legs |
| `feet` | both feet |

### Worked example

```js
parts: {
  head:         '/head',
  arms:         '/upper-limb',       // 2 regions in simple mode, 8 in detailed
  legs:         '/lower-limb',
  'hand-left':  '/hand-therapy',     // wins over `arms` for this one region
  pelvis:       false                // drawn, but not clickable
}
```

In detailed mode that gives: head → `/head`; the left hand → `/hand-therapy`;
the seven other arm segments → `/upper-limb`; all eight leg segments →
`/lower-limb`; pelvis drawn but inert; neck, chest and abdomen drawn but inert.

## Regions with no link

A region with no URL is still drawn, in the "unlinked" colour, and is not
clickable. To hide unlinked regions entirely, set `showDisabled` to `false`
(`data-show-disabled="false"`, or untick *Draw regions that have no link* in
WordPress).

Giving a region a label but no URL keeps its tooltip while leaving it inert —
useful for naming a region you have nothing to link to yet.

## Groups

Each region belongs to a group, exposed as `data-bodymap-group` in the markup. That
is what simple mode collapses, and it gives you a CSS handle for a whole limb:

```css
.bodymap__region[data-bodymap-group="leg-left"] { --bodymap-fill-hover: #f59e0b; }
```

| Group | Regions in detailed mode |
|---|---|
| `head` | `head` |
| `neck` | `neck` |
| `chest` | `chest` |
| `abdomen` | `abdomen` |
| `pelvis` | `pelvis` |
| `arm-right` | `shoulder-right`, `upper-arm-right`, `forearm-right`, `hand-right` |
| `arm-left` | `shoulder-left`, `upper-arm-left`, `forearm-left`, `hand-left` |
| `leg-right` | `thigh-right`, `knee-right`, `lower-leg-right`, `foot-right` |
| `leg-left` | `thigh-left`, `knee-left`, `lower-leg-left`, `foot-left` |
