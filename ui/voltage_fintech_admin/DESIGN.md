---
name: Voltage Fintech Admin
colors:
  surface: '#12131a'
  surface-dim: '#12131a'
  surface-bright: '#383940'
  surface-container-lowest: '#0c0e14'
  surface-container-low: '#1a1b22'
  surface-container: '#1e1f26'
  surface-container-high: '#282a31'
  surface-container-highest: '#33343c'
  on-surface: '#e2e1eb'
  on-surface-variant: '#c4c9ac'
  inverse-surface: '#e2e1eb'
  inverse-on-surface: '#2f3037'
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
  secondary-container: '#4a4949'
  on-secondary-container: '#bab8b7'
  tertiary: '#ffffff'
  on-tertiary: '#303030'
  tertiary-container: '#e5e2e1'
  on-tertiary-container: '#656464'
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
  on-secondary-fixed-variant: '#474646'
  tertiary-fixed: '#e5e2e1'
  tertiary-fixed-dim: '#c8c6c5'
  on-tertiary-fixed: '#1b1b1c'
  on-tertiary-fixed-variant: '#474746'
  background: '#12131a'
  on-background: '#e2e1eb'
  surface-variant: '#33343c'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  title-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  data-table:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 16px
  label-caps:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
spacing:
  sidebar_width: 280px
  topbar_height: 64px
  gutter: 24px
  margin: 32px
  base_unit: 4px
---

## Brand & Style
The design system is engineered for high-stakes financial data management, catering to B2B fintech operators who require speed, precision, and clarity. The brand personality is technical, high-performance, and uncompromisingly modern. 

The aesthetic is a fusion of **Minimalism** and **Technical Brutalism**. It prioritizes information density without sacrificing legibility. Visual depth is achieved through structural 1px borders and distinct tonal layers rather than shadows or gradients. The interface should feel like a high-frequency trading terminal: dark, focused, and electrically responsive.

## Colors
The palette is rooted in a deep charcoal foundation to minimize eye strain during long sessions.
- **Primary:** Electric Lime (#CCFF00) is used exclusively for primary actions, progress indicators, and critical focus states.
- **Surface Layers:** The base background is #131313. Secondary containers (sidebar, cards) utilize #1E1E1E to create subtle structural separation.
- **Semantic Accents:** Used sparingly for status and data trends:
  - **Emerald (#10B981):** "Paid" status, positive growth.
  - **Amber (#F59E0B):** "Pending" status, caution.
  - **Coral (#F43F5E):** "Arrears" status, negative trends.
- **Borders:** A consistent #2D2D2D is used for all structural outlines, ensuring a crisp, "engineered" look.

## Typography
This design system utilizes **Inter** exclusively to maintain a systematic, utilitarian feel. 
- **Tabular Figures:** For all financial data and tables, `font-variant-numeric: tabular-nums` must be enabled to ensure columns of numbers align perfectly.
- **Hierarchy:** High contrast is maintained between the Electric Lime or White headers and the Muted Grey body text.
- **Labels:** Use uppercase for small labels and table headers to provide a structural anchor for the data below.

## Layout & Spacing
The layout follows a rigorous 12-column grid system for the main content area.
- **Sidebar:** A persistent 280px left sidebar houses the primary navigation. It uses a slightly elevated surface color (#1E1E1E) and a right-hand border.
- **Topbar:** A 64px fixed header contains breadcrumbs for path-finding and the user profile/notifications on the right.
- **Data Density:** Spacing is tight (4px base unit) to allow for maximum data visibility without scrolling. Data tables should use 12px vertical padding on rows to maintain a professional, high-density look.
- **Breakpoints:**
  - **Desktop (1440px+):** Full 12-column grid.
  - **Laptop (1024px - 1439px):** Sidebar remains fixed, grid columns compress.

## Elevation & Depth
This design system eschews shadows in favor of **Tonal Layering** and **Structural Outlines**.
- **Level 0:** #131313 (Global Background).
- **Level 1:** #1E1E1E (Cards, Sidebar, Modals).
- **Outlines:** Every interactive element and container is defined by a 1px border (#2D2D2D).
- **Interaction Depth:** On hover, table rows and interactive cards should shift their background to #252525. There is no physical "lift"; the change is purely chromatic.

## Shapes
The shape language is **Sharp (0px)**. All containers, buttons, inputs, and badges use 90-degree corners. This reinforces the "Voltage" brand's precision and engineering-first philosophy. The only exception is for circular user avatars or specific status pips within badges.

## Components
- **Data Tables:** Headers are `label-caps`. Rows have a 1px bottom border. Hover state uses #252525 background. Financial columns must be right-aligned with tabular figures.
- **KPI Cards:** Display a `title-sm` label, a `display-lg` value, and a simplified sparkline (Emerald or Coral) at the bottom.
- **Status Badges:** Solid background with high-contrast text.
  - *Standard:* Grey scale.
  - *Trusted:* Electric Lime background, Black text.
  - *Preferred:* White background, Black text.
- **Primary Buttons:** Electric Lime (#CCFF00) background, Black (#000000) text, bold weight, sharp corners.
- **Input Fields:** 1px #2D2D2D border, #131313 background. Focus state changes border to Electric Lime.
- **Sidebar Navigation:** Active links use an Electric Lime left-accent border (2px) and white text; inactive links use #A1A1AA.