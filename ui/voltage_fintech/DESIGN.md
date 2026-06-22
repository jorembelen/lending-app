---
name: Voltage Fintech
colors:
  surface: '#131313'
  surface-dim: '#131313'
  surface-bright: '#3a3939'
  surface-container-lowest: '#0e0e0e'
  surface-container-low: '#1c1b1b'
  surface-container: '#201f1f'
  surface-container-high: '#2a2a2a'
  surface-container-highest: '#353534'
  on-surface: '#e5e2e1'
  on-surface-variant: '#c4c9ac'
  inverse-surface: '#e5e2e1'
  inverse-on-surface: '#313030'
  outline: '#8e9379'
  outline-variant: '#444933'
  surface-tint: '#abd600'
  primary: '#ffffff'
  on-primary: '#283500'
  primary-container: '#c3f400'
  on-primary-container: '#556d00'
  inverse-primary: '#506600'
  secondary: '#c8c6c5'
  on-secondary: '#313030'
  secondary-container: '#474746'
  on-secondary-container: '#b7b5b4'
  tertiary: '#ffffff'
  on-tertiary: '#68000f'
  tertiary-container: '#ffdad8'
  on-tertiary-container: '#b6353a'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#c3f400'
  primary-fixed-dim: '#abd600'
  on-primary-fixed: '#161e00'
  on-primary-fixed-variant: '#3c4d00'
  secondary-fixed: '#e5e2e1'
  secondary-fixed-dim: '#c8c6c5'
  on-secondary-fixed: '#1c1b1b'
  on-secondary-fixed-variant: '#474746'
  tertiary-fixed: '#ffdad8'
  tertiary-fixed-dim: '#ffb3b0'
  on-tertiary-fixed: '#410006'
  on-tertiary-fixed-variant: '#8c1520'
  background: '#131313'
  on-background: '#e5e2e1'
  surface-variant: '#353534'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  margin-mobile: 20px
  gutter: 16px
  touch-target-min: 48px
  stack-sm: 12px
  stack-md: 24px
  stack-lg: 40px
---

## Brand & Style

The brand personality is high-energy, authoritative, and ultra-efficient. Designed for field loan collectors, the UI prioritizes speed of recognition and physical ease of use in diverse lighting conditions. 

The design style is **High-Contrast Modernism**. It utilizes a "Near Black" foundation to reduce eye strain during extended outdoor use, punctuated by "Electric Lime" to draw immediate focus to primary actions and critical financial data. The aesthetic is lean and functional, stripping away decorative elements in favor of raw typographic hierarchy and clear structural boundaries. It evokes a sense of technical precision and urgency, ensuring the user feels in control of high-stakes financial interactions.

## Colors

The palette is optimized for high-performance dark mode. 

- **Primary (Electric Lime):** Reserved exclusively for high-priority actions, active toggle states, and "Success" indicators. It must always be paired with black text for accessibility.
- **Background (Near Black):** The base layer for all screens to maximize contrast with the lime accent.
- **Surface (Dark Charcoal):** Used for cards, input fields, and bottom sheets to create subtle depth without relying on heavy shadows.
- **Warning (Coral):** Used sparingly for overdue payments, variances, or negative balances. Its desaturated nature prevents it from vibrating against the dark background while still signaling caution.

## Typography

This design system uses **Inter** for its exceptional legibility in digital interfaces and its neutral, professional tone. 

- **Numerical Data:** Currency values and loan IDs should use `display-lg` or `headline-lg` to ensure they are readable at a glance or in bright sunlight.
- **Hierarchy:** Use font weight (SemiBold/Bold) rather than color shifts to establish hierarchy, as desaturated grays can become illegible in field conditions.
- **Labels:** Small labels use increased letter spacing and uppercase styling to distinguish them from body content.

## Layout & Spacing

The layout is optimized for **one-handed mobile use**. 

- **Grid:** A fluid 4-column mobile grid with 20px side margins. 
- **Vertical Rhythm:** Elements are stacked using a strict 8px baseline grid. Large "Stack" increments (24px, 40px) are used between distinct logical sections (e.g., Customer Info vs. Payment Input) to prevent accidental taps.
- **Touch Targets:** All interactive elements (buttons, list items, checkboxes) must maintain a minimum height of 48px. 
- **Scanning:** Information is prioritized from top-to-bottom, with the most critical action (e.g., "Collect Payment") pinned to the bottom of the screen in a "Safe Area" container.

## Elevation & Depth

This design system eschews traditional shadows in favor of **Tonal Layering** and **Subtle Outlines**.

- **Level 0 (Background):** Near Black (#0A0A0A) for the main canvas.
- **Level 1 (Surface):** Dark Charcoal (#1A1A1A) for cards and primary containers.
- **Level 2 (Active/Overlay):** A subtle 1px border using #FFFFFF at 10% opacity is applied to Level 1 surfaces to define edges. 
- **Depth:** No blur-based shadows are used. Depth is communicated strictly through the contrast between the #0A0A0A floor and the #1A1A1A cards. For modal sheets, a background dim of 60% black is applied.

## Shapes

The shape language is "Rounded" to soften the high-contrast aesthetic and make the app feel approachable despite its dark, technical color palette.

- **Primary Containers:** Cards and input fields use a **16px (rounded-lg)** radius.
- **Buttons:** Large action buttons use a **16px (rounded-lg)** radius to match the containers.
- **Badges/Tags:** Use a **pill-shaped (rounded-xl)** radius to clearly distinguish status indicators from interactive buttons.
- **Selection Controls:** Checkboxes use a 4px radius, while radio buttons remain circular.

## Components

- **Primary Buttons:** Solid Electric Lime background with #0A0A0A text. Height: 56px. Font: Label-md Bold.
- **Secondary Buttons:** Dark Charcoal background with a 1px white (10% alpha) border. Text: White.
- **Cards:** Background #1A1A1A, 16px corner radius. Internal padding should be 20px to ensure content doesn't feel cramped.
- **Input Fields:** #1A1A1A background, 16px radius, with 1px border on focus (Electric Lime). Labels should be persistent and placed above the field.
- **Status Badges:** High-contrast containers. For "Overdue," use a Coral background with White text. For "Paid," use a Subtle Lime (20% alpha) background with Lime text.
- **List Items:** 64px minimum height. Include a 1px separator (#FFFFFF at 5% alpha) between items to maintain horizontal alignment without adding visual clutter.
- **Key Metrics:** Large numerical displays (Currency) should always be in White or Electric Lime, never desaturated.