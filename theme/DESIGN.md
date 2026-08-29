---
name: Ethereal Heritage
colors:
  surface: '#f8faf6'
  surface-dim: '#d9dad7'
  surface-bright: '#f8faf6'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f1'
  surface-container: '#edeeeb'
  surface-container-high: '#e7e9e5'
  surface-container-highest: '#e1e3e0'
  on-surface: '#191c1a'
  on-surface-variant: '#3e4a3f'
  inverse-surface: '#2e312f'
  inverse-on-surface: '#f0f1ee'
  outline: '#6e7a6e'
  outline-variant: '#bdcabc'
  surface-tint: '#006d36'
  primary: '#006d36'
  on-primary: '#ffffff'
  primary-container: '#50c878'
  on-primary-container: '#005025'
  inverse-primary: '#66dd8b'
  secondary: '#625f4f'
  on-secondary: '#ffffff'
  secondary-container: '#e5e0cc'
  on-secondary-container: '#666353'
  tertiary: '#735c00'
  on-tertiary: '#ffffff'
  tertiary-container: '#d3ae36'
  on-tertiary-container: '#544200'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#83fba5'
  primary-fixed-dim: '#66dd8b'
  on-primary-fixed: '#00210c'
  on-primary-fixed-variant: '#005227'
  secondary-fixed: '#e8e2cf'
  secondary-fixed-dim: '#ccc6b3'
  on-secondary-fixed: '#1e1c10'
  on-secondary-fixed-variant: '#4a4738'
  tertiary-fixed: '#ffe088'
  tertiary-fixed-dim: '#e9c349'
  on-tertiary-fixed: '#241a00'
  on-tertiary-fixed-variant: '#574500'
  background: '#f8faf6'
  on-background: '#191c1a'
  surface-variant: '#e1e3e0'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 64px
    fontWeight: '700'
    lineHeight: 72px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: DM Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: DM Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-caps:
    fontFamily: DM Sans
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.15em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 8px
  section-gap: 120px
  container-padding: 24px
  gutter: 32px
---

## Brand & Style
The design system is centered on timeless elegance and the quiet luxury of traditional wedding celebrations. The target audience includes couples seeking a sophisticated, heirloom-quality digital presence that feels as intentional as a physical invitation. 

The style blends **Minimalism** with **Skeuomorphic accents**. It utilizes generous white space (macro-typography) to create a sense of air and importance. Visual interest is generated through high-contrast serif typography and the use of gold-foil-inspired highlights. The emotional response should be one of serenity, reverence, and enduring grace.

## Colors
This design system uses a palette rooted in nature and prestige. 
- **Pearl White (#F0EAD6):** The primary background color. It provides a warmer, more organic feel than pure white, mimicking high-quality cardstock.
- **Emerald Green (#50C878):** Used for primary actions and deep accents, representing growth and vitality.
- **Gold Accent (#D4AF37):** Used sparingly for decorative elements, rules, and small interactive states to denote luxury.
- **Neutral Charcoal (#2D302E):** Used for body text to ensure high legibility against the Pearl White background while maintaining a softer look than true black.

## Typography
The typography strategy relies on the contrast between the highly expressive, high-contrast **Playfair Display** and the understated, geometric **DM Sans**. 

- **Headlines:** Should be set in Playfair Display. For specific emphasis, use italic styles for names or dates.
- **Body Text:** DM Sans provides a clean, functional counterpoint that ensures wedding details (registry, location, RSVP) are easily digestible.
- **Micro-copy:** Use `label-caps` for section headers and button labels to evoke the style of traditional stationary layouts.

## Layout & Spacing
This design system utilizes a **Fixed Grid** model to maintain the look of a curated editorial piece. 
- **Desktop:** 12-column grid with a 1140px max-width.
- **Tablet:** 8-column grid with 32px margins.
- **Mobile:** 4-column grid with 20px margins.

Spacing is intentionally large to prevent the UI from feeling cluttered. Sections should be separated by `section-gap` to allow the eye to rest. Centered alignment is preferred for hero and introductory sections to reinforce a formal tone.

## Elevation & Depth
Depth is achieved through **Tonal Layers** and subtle **Ambient Shadows** rather than aggressive elevation.
- **Surfaces:** Most content sits directly on the Pearl White background. 
- **Cards:** Use a very thin, 1px border in a slightly darker shade of Pearl White or a 0.5px Gold border.
- **Shadows:** Use extremely soft, long shadows with a 5% opacity Emerald Green tint for "floating" elements like RSVP modals to keep the aesthetic feeling light and airy.
- **Accents:** Use thin horizontal rules (1px) in Gold (#D4AF37) to separate logical sections within a page.

## Shapes
The shape language is conservative and structured. 
- **Buttons and Inputs:** Use a "Soft" radius (0.25rem) to provide a hint of approachability without losing the formal edge.
- **Cards:** Stick to sharp or slightly softened corners. 
- **Images:** Photography should utilize larger radius corners (rounded-lg) or custom "arch" masks to mimic traditional window panes or classic stationery frames.

## Components
- **Buttons:** 
    - *Primary:* Emerald Green background with Pearl White text. All-caps typography.
    - *Secondary:* Transparent background with an Emerald Green or Gold 1px border.
- **Cards:** Use for "Schedule of Events" or "Bridal Party" profiles. Cards should have a subtle background shift (5% darker than Pearl White) and no heavy shadows.
- **Input Fields:** Bottom-border only (classical style) or 1px stroke. The focus state should transition the border color to Gold.
- **RSVP Form:** Treat as a focal point. Use large typography and clear checkboxes with Emerald Green fills.
- **Decorative Dividers:** Incorporate custom SVG icons like a small leaf or a gold dot to break up long text blocks.
- **Interactive Map:** Use a custom-styled map with a desaturated, cream-colored base and Emerald Green pins.