---
name: Wędruj z Nami
colors:
  surface: '#f7fbf1'
  surface-dim: '#d7dbd2'
  surface-bright: '#f7fbf1'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f1f5eb'
  surface-container: '#ebefe6'
  surface-container-high: '#e5eae0'
  surface-container-highest: '#dfe4da'
  on-surface: '#181d17'
  on-surface-variant: '#40493e'
  inverse-surface: '#2d322b'
  inverse-on-surface: '#eef2e8'
  outline: '#707a6d'
  outline-variant: '#bfcaba'
  surface-tint: '#176d29'
  primary: '#136a27'
  on-primary: '#ffffff'
  primary-container: '#33843d'
  on-primary-container: '#f7fff1'
  inverse-primary: '#86d988'
  secondary: '#5b614c'
  on-secondary: '#ffffff'
  secondary-container: '#dfe5cb'
  on-secondary-container: '#616752'
  tertiary: '#9a3a5b'
  on-tertiary: '#ffffff'
  tertiary-container: '#b95274'
  on-tertiary-container: '#fffbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#a1f6a1'
  primary-fixed-dim: '#86d988'
  on-primary-fixed: '#002106'
  on-primary-fixed-variant: '#005319'
  secondary-fixed: '#dfe5cb'
  secondary-fixed-dim: '#c3c9b0'
  on-secondary-fixed: '#181d0e'
  on-secondary-fixed-variant: '#434936'
  tertiary-fixed: '#ffd9e1'
  tertiary-fixed-dim: '#ffb1c5'
  on-tertiary-fixed: '#3f001b'
  on-tertiary-fixed-variant: '#7f2546'
  background: '#f7fbf1'
  on-background: '#181d17'
  surface-variant: '#dfe4da'
  sky-blue: '#BAE1FF'
  earthy-sand: '#F4EBD0'
  unesco-gold: '#D4AF37'
  error-red: '#C84C4C'
  surface-cream: '#FCFAF5'
typography:
  headline-xl:
    fontFamily: Montserrat
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Montserrat
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Montserrat
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
  headline-md:
    fontFamily: Montserrat
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
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 64px
  max-width: 1280px
---

## Brand & Style

The brand identity for this design system is rooted in the heritage of the Polish Tourist and Sightseeing Society (PTTK), modernized for a contemporary digital audience. It evokes a sense of **outdoor exploration, reliability, and community**. The target audience ranges from active students and teachers to seasoned tour guides and casual weekend tourists, requiring an interface that balances professional data density with an inviting, accessible atmosphere.

The design style follows a **Corporate / Modern** approach with a strong **Tactile** influence. It prioritizes clarity and utility—essential for a catalog—while using soft geometry and nature-inspired colors to feel warm and welcoming. 

### Visual Pillars
- **Clarity & Trust:** A structured layout that reinforces PTTK’s position as a definitive source of historical and geographical knowledge.
- **Organic Geometry:** The use of medium rounded corners and pill-shaped elements reflects the softness of natural landscapes (hills, paths, stones).
- **Breathable Utility:** Ample whitespace ensures that data-heavy object descriptions and complex maps remain legible and non-intimidating.

## Colors

The palette is anchored by a deep, trustworthy **Forest Green** (Primary), representing the core identity of PTTK. This is complemented by a series of nature-derived pastels that provide organizational clarity without adding visual noise.

### Palette Application
- **Primary (#42934A):** Used for key actions, brand touchpoints, and the "published/official" status markers.
- **Secondary / Sage (#E2E8CE):** Used for large container backgrounds and decorative elements to soften the interface.
- **Sky Blue (#BAE1FF):** Reserved for technical/practical information, weather-related UI, or water-based natural sites.
- **Earthy Sand (#F4EBD0):** Employed for historical site categorization or secondary call-to-action sections.
- **UNESCO Gold (#D4AF37):** A specific accent color used exclusively for the UNESCO designation badge to signify importance.
- **Neutral:** We use a warm "Surface Cream" instead of pure white to reduce eye strain during long reading sessions and to enhance the tactile feel.

## Typography

The typography system uses a pairing of **Montserrat** for headlines and **Inter** for body text. This combination ensures that the catalog feels authoritative yet modern.

- **Headlines (Montserrat):** Used for object titles, section headers, and news titles. The geometric nature of Montserrat provides a clean, impactful "signpost" for users navigating the catalog.
- **Body & Labels (Inter):** Inter is chosen for its exceptional legibility at small sizes, which is critical for the "Practical Information" and "Full Description" sections of the object pages.
- **Hierarchy:** High contrast between headline weights and body sizes is encouraged to guide the user through long-form editorial content.

## Layout & Spacing

This design system utilizes a **Fluid Grid** with fixed maximum constraints to ensure a comfortable reading experience on ultra-wide monitors.

### Spacing Principles
- **Grid:** A 12-column grid for desktop, 8-column for tablet, and 4-column for mobile.
- **Rhythm:** An 8px base unit (derived from 4px increments) governs all margins and paddings.
- **Map View:** The catalog uses a "Split View" on desktop—a fixed map on the left or right (50% width) with a scrollable list of objects. On mobile, this transitions to a toggle between "List" and "Map" views to maximize screen real estate.
- **Content Density:** For editorial pages (News and Object Details), a centered single-column layout with a max-width of 800px is used for the text body to ensure optimal line length.

## Elevation & Depth

Hierarchy is established through **Tonal Layers** and **Ambient Shadows**. This approach mimics the way maps or physical brochures are layered.

- **Level 0 (Base):** The `surface-cream` background.
- **Level 1 (Cards/Containers):** Elevated slightly with a very soft, diffused shadow (10% opacity Primary color tint) to distinguish objects from the background.
- **Level 2 (Navigation/Popups):** Higher elevation using a slightly tighter shadow to indicate interactivity and "floating" map markers.
- **Interactivity:** Buttons and interactive cards should use a subtle "press" effect (reducing shadow or shifting color) rather than a heavy lift, maintaining the grounded, reliable feel of the brand.

## Shapes

The shape language is consistently **Rounded**, reflecting a friendly and approachable personality. 

- **Containers & Cards:** Use a standard 12px-16px radius (`rounded-lg`).
- **Interactive Elements:** Buttons and Chips use a "Pill" shape (full rounding) to clearly distinguish them from informational containers.
- **Media:** Images in the gallery and the main object photo should follow the card roundedness to maintain a cohesive visual rhythm.
- **Map Markers:** Teardrop shapes for points, but with softened edges to match the overall UI.

## Components

### Buttons
- **Primary Button:** Pill-shaped, Primary Green background, white Montserrat Label. High contrast for "View Map" or "Contact PTTK" actions.
- **Secondary Button:** Pill-shaped, Primary Green outline with a transparent or cream background. Used for "Show More" or "Filter" actions.

### Chips & Badges
- **Object Type Chips:** Small, pill-shaped tags with a subtle pastel background (e.g., Sky Blue for "Natural Sites").
- **UNESCO Badge:** A specialized component with a Gold border and icon, placed at the top right of cards or header sections.

### Cards
- **Object Card:** Vertical layout. Image at the top (fixed aspect ratio), title in Montserrat MD, followed by the Object Type Chip and a short "Lead" text.
- **News Card:** Horizontal layout to differentiate from the catalog, emphasizing the publication date.

### Input Fields & Search
- **Search Bar:** Large, pill-shaped field with a subtle shadow and a clear Primary Green search icon. 
- **Filters:** Large checkboxes and radio buttons with custom Primary Green styling to ensure they are touch-friendly for hikers on mobile devices.

### Map Elements
- **Clustering:** Markers cluster into rounded circles with a Primary Green border and a numerical count.
- **Popup:** Clean, white background with a small thumbnail, title, and "View Details" button. High elevation to sit clearly above map layers.