---
name: Kinetic Finance
colors:
  surface: '#111319'
  surface-dim: '#111319'
  surface-bright: '#373940'
  surface-container-lowest: '#0c0e14'
  surface-container-low: '#191b22'
  surface-container: '#1e1f26'
  surface-container-high: '#282a30'
  surface-container-highest: '#33343b'
  on-surface: '#e2e2eb'
  on-surface-variant: '#c4c9ac'
  inverse-surface: '#e2e2eb'
  inverse-on-surface: '#2e3037'
  outline: '#8e9379'
  outline-variant: '#444933'
  surface-tint: '#abd600'
  primary: '#ffffff'
  on-primary: '#283500'
  primary-container: '#c3f400'
  on-primary-container: '#556d00'
  inverse-primary: '#506600'
  secondary: '#ffdb9d'
  on-secondary: '#412d00'
  secondary-container: '#feb700'
  on-secondary-container: '#6b4b00'
  tertiary: '#ffffff'
  on-tertiary: '#68000d'
  tertiary-container: '#ffdad7'
  on-tertiary-container: '#bd2c32'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#c3f400'
  primary-fixed-dim: '#abd600'
  on-primary-fixed: '#161e00'
  on-primary-fixed-variant: '#3c4d00'
  secondary-fixed: '#ffdea8'
  secondary-fixed-dim: '#ffba20'
  on-secondary-fixed: '#271900'
  on-secondary-fixed-variant: '#5e4200'
  tertiary-fixed: '#ffdad7'
  tertiary-fixed-dim: '#ffb3af'
  on-tertiary-fixed: '#410005'
  on-tertiary-fixed-variant: '#920418'
  background: '#111319'
  on-background: '#e2e2eb'
  surface-variant: '#33343b'
typography:
  display:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 34px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 30px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  amount-lg:
    fontFamily: JetBrains Mono
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.05em
  amount-sm:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
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
  base: 4px
  xs: 8px
  sm: 16px
  md: 24px
  lg: 32px
  xl: 48px
  container-padding: 20px
  card-gap: 12px
---

## Brand & Style

This design system is built for a mobile-first micro-lending experience that balances high-impact urgency with financial approachability. The style is a "Warm Neobank" aesthetic—merging the aggressive contrast of fintech with soft, organic geometry to build trust. 

The brand personality is **energetic, transparent, and empowering**. It avoids the coldness of traditional banking by using generous whitespace and glowing accents, making the borrowing process feel like a positive momentum shift rather than a burden. The visual narrative focuses on "The Path to Success," using verticality and light-play to guide the user toward their next financial milestone.

## Colors

The palette is optimized for high legibility in low-light environments. 
- **Primary (Electric Lime):** Used exclusively for "Call to Success" actions, progress indicators, and active states. It should feel like it's emitting light.
- **Surface Strategy:** The background uses a deep charcoal to reduce eye strain, while `surface-bright` is used for interactive cards to create a clear visual hierarchy.
- **Semantic Logic:** Success is tied to the Primary color to reinforce positive financial behavior. Warning and Error states use warm, desaturated tones to remain visible without feeling overly punitive.

## Typography

The system utilizes **Inter** for all functional and editorial text to maintain a clean, neutral tone. For financial data, **JetBrains Mono** is introduced; its tabular figures ensure that currency amounts remain aligned and legible, preventing visual "jumping" when numbers update.

- **Headlines:** Use tight letter-spacing and bold weights to command attention.
- **Financial Figures:** Always use the `amount` roles. These should be treated as primary UI elements, often larger than the surrounding text.
- **Hierarchy:** Use `label-caps` for metadata (e.g., "NEXT PAYMENT") to distinguish status from content.

## Layout & Spacing

This is a **fluid, mobile-first layout** that prioritizes a single-column stack. 
- **The Vertical Timeline:** Information should flow vertically. Use `md` (24px) spacing between major sections and `card-gap` (12px) between related items within a section.
- **Touch Targets:** All interactive elements must maintain a minimum height of 48px, though primary buttons should favor 56px for better ergonomics on mobile devices.
- **Safe Areas:** Horizontal margins are fixed at `container-padding` (20px) to ensure content doesn't bleed into the edges of modern edge-to-edge displays.

## Elevation & Depth

Depth in this system is achieved through **Tonal Layering** and **Subtle Glows** rather than traditional drop shadows.
- **Level 0 (Background):** #0C0E14.
- **Level 1 (Cards/Surfaces):** #1A1C24 with a 1px solid border of #2D2F39 (Outline).
- **Level 2 (Active/Floating):** Use a 12% opacity primary-colored outer glow (`#CCFF00`) for active states or highlighted loan offers.
- **Interactive States:** When a card is pressed, it should scale slightly (98%) and the border color should shift to the primary color.

## Shapes

The shape language is defined by **exaggerated, friendly radii**. 
- **Standard Cards:** Use `rounded-xl` (24px) to create a soft, modern container that contrasts with the bold typography.
- **Buttons & Inputs:** Use `rounded-lg` (16px) for a slightly more structured but still approachable feel.
- **Progress Bars:** Should be fully rounded (pill-shaped) to represent a continuous, smooth journey toward repayment.

## Components

### Buttons
- **Primary:** Background `#CCFF00`, Text `#000000`, 16px corner radius. High-gloss finish.
- **Secondary:** Background transparent, Border 2px `#2D2F39`, Text `#FFFFFF`.
- **Tertiary:** Text `#CCFF00`, no background, underline on hover.

### Cards
- Always use `#1A1C24` as the base.
- Internal padding should be `md` (24px) for prominent data (Loan Balance) and `sm` (16px) for secondary lists.

### Input Fields
- Dark backgrounds (`#0C0E14`) with a persistent `#2D2F39` border.
- On focus, the border transitions to `#CCFF00` with a subtle 4px blur glow.

### Progress Trackers
- Use vertical "Steppers" for loan application stages. 
- Active steps use the Primary Electric Lime; incomplete steps use the Outline color.

### Financial Displays
- Large currency symbols (e.g., "$") should be 60% the size of the numerical amount to keep the focus on the value.