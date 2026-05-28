# UX & UI Specification — Sightseeing Objects Catalog of Poland

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*; "news listing" = *lista wpisów*

This document translates the PRD and design decisions into a UX/UI specification ready for direct use by a frontend developer or interface generation tool.

The scope covers a public web application, a map-first catalog, object pages, a homepage, a News section, and a simple CMS for the editorial team.

---

## 1. Information Architecture

### Screen/Page Map with Hierarchy

```text
Public
/
  Homepage

/katalog
  Object catalog with map, filters, and result list

/obiekty/[slug]
  Object detail page

/aktualnosci
  Article listing page

/aktualnosci/[slug]
  Article detail page

/kontakt
  Optional contact page or contact section accessible from footer/nav

CMS
/cms/login
  Editor login

/cms
  CMS Dashboard

/cms/obiekty
  Object listing

/cms/obiekty/new
  Add object

/cms/obiekty/[id]
  Edit object

/cms/aktualnosci
  Article listing

/cms/aktualnosci/new
  Add article

/cms/aktualnosci/[id]
  Edit article
```

### Content Grouping & Component Organization

The public application should have three main areas:

- **Object Discovery:** catalog, map, search, filters, detail pages.
- **Project Context:** homepage, purpose description, value for tourists and teachers.
- **Editorial Content:** News, articles, entry points to catalog/map.

The CMS should remain simple and operational:

- **Objects:** list, add, edit, publish/unpublish, media, location.
- **News:** list, add, edit, publish/unpublish, Markdown.

### Navigation Structure & Patterns

Main public navigation:

- Logo / project name
- Map
- Catalog
- News
- Contact

Preferred model:

- CTA on the homepage leads primarily to the map/catalog view.
- "Map" link may lead to `/katalog` with the map as the active entry point.
- "Catalog" link leads to the same view, but oriented toward filtering and results.

CMS:

- Sidebar or top navigation with sections: Objects, News.
- Visible publication status for each record.
- "Unpublish" action as a safe alternative to deletion.

### Layout Zones & Content Blocks

Homepage:

```text
[Header]
[Hero / informational intro]
  Title, short project description
  Primary CTA: Show Map
  Secondary CTA: Browse Catalog

[For Whom and Why]
  Tourists, teachers, trip planners

[Latest Objects]
  Row of 3-4 cards

[News teaser]
  Few recent posts or entry to News section

[Contact / feedback prompt]
[Footer]
```

Desktop Catalog:

```text
[Header]

[Left filter sidebar]  [Main discovery area]
                       [Search]
                       [Active filter chips + result count]
                       [Map]
                       [Card grid results]
```

Mobile Catalog:

```text
[Header]
[Search]
[Map | List segmented control] [Filters]

Default: Map

Map view:
  [Full-width map]
  [Pin popup / bottom preview when selected]

List view:
  [Card grid/list adapted to one column]

Filters:
  [Bottom sheet]
```

Object Detail Page:

```text
[Header]
[Back to catalog]

[Title]
[Metadata row: voivodeship | category | UNESCO badge]
[Main photo]
[Gallery thumbnails if more images exist]
[Description]
[Practical info]
[Print button]
[Nearby objects card grid]
[Footer]
```

### Responsive Behavior Guidelines

- Desktop: filters permanently visible on the left, map first in the central area, results below.
- Tablet: filters may move to a side panel or "Filters" button, map remains above results.
- Mobile: map by default, Map/List switcher, filters as a bottom sheet.
- Object cards on mobile should be single-column.
- Map on mobile must have sufficient height to be usable, minimum approximately 50-60% of viewport height in map mode.

---

## 2. Core User Flows

### Primary User Journey: Planning a Visit

1. User enters the homepage.
2. Reads a short description of the project's purpose.
3. Selects "Show Map".
4. Arrives at the catalog with visible map and filters.
5. Narrows results by voivodeship, category, or UNESCO.
6. Results update immediately on the map and card grid.
7. User clicks a pin on the map.
8. Sees a popup with image, title, and "View Object" link.
9. Navigates to the object detail page.
10. Reads the description, checks practical info and nearby objects.
11. Optionally prints the object page.

### Search and Filter Flow

```text
User enters query
  -> Search applies within currently selected filters
  -> Result count updates
  -> Map markers/polygons update
  -> Active chips update
  -> Card grid updates
```

Filters:

- Voivodeship: single select.
- Category: accordion/tree up to 3 levels.
- UNESCO: toggle/checkbox.
- Clear filters: restores the full result set.

Filters apply immediately on change, without an "Apply" button.

### Nearby Objects Flow

On the object detail page:

1. System knows the location of the currently displayed object.
2. Fetches nearby objects.
3. Default radius: 5 km.
4. If no results, the system expands the radius to 20 km.
5. Results are shown as a card grid.

In user geolocation mode:

1. User selects the nearby objects feature.
2. System requests location access.
3. After consent, shows objects within 5 km.
4. If no results, expands to 20 km.
5. If the user declines, shows a message and an alternative: use the map or filters.

### Article Flow

1. User selects "News".
2. Sees a grid of posts with date, title, optional cover, and excerpt.
3. Opens a post.
4. Reads the Markdown content rendered as an article.
5. At the end, sees a contextual CTA, e.g., "Show Map", "Browse Catalog", or a link to filtered objects.

### CMS Editorial Flow: Object

1. Editor logs in to the CMS.
2. Selects "Objects".
3. Sees a list of objects with publication status.
4. Adds or edits an object.
5. Fills in required fields: title, description, minimum one photo.
6. Adds coordinates.
7. Optionally adds practical data, multiple photos, category, UNESCO, area geometry.
8. Saves.
9. After publishing, the object appears in the catalog, on the map, and in search results.

### CMS Editorial Flow: Article

1. Editor selects "News".
2. Creates a new post.
3. Fills in title, publication date, and Markdown content.
4. Optionally adds a cover photo.
5. Saves and publishes.
6. Post appears on the News listing.

### Decision Points & UI Branches

- No results after filtering: show empty state with option to clear filters.
- No results within 5 km: automatic expansion to 20 km, with a message.
- No results within 20 km: show a message and link to catalog/map.
- No additional photo: don't show gallery, use only the main photo.
- Unpublished object: does not appear publicly.
- Unpublished post: does not appear publicly.
- Geolocation declined: show a fallback based on map and filters.

### Flow Diagram Description for Mermaid

```text
Homepage --> CatalogMap
CatalogMap --> ApplyFilters
ApplyFilters --> UpdatedMapAndGrid
UpdatedMapAndGrid --> PinPopup
PinPopup --> ObjectDetail
UpdatedMapAndGrid --> ObjectCard
ObjectCard --> ObjectDetail
ObjectDetail --> PrintPage
ObjectDetail --> NearbyObjects

Homepage --> NewsList
NewsList --> ArticleDetail
ArticleDetail --> ContextualCTA
ContextualCTA --> CatalogMap
```

---

## 3. View Specifications

### Homepage

Purpose: explain the project and route users into discovery.

Primary layout:

- Informational intro, not a marketing-heavy landing page.
- Primary CTA: "Show Map".
- Secondary CTA: "Browse Catalog".
- Latest objects row: 3-4 cards.
- News teaser.
- Small contact prompt near the bottom.

Homepage object cards:

- Image
- Title
- Short location/category context
- Link to detail page

### Catalog View

Desktop layout:

- Left sidebar: filters.
- Main area:
  - Search bar.
  - Active filter chips and result count.
  - Map.
  - Results card grid.

Filter sidebar components:

- Voivodeship select/list.
- Category accordion/tree up to 3 levels.
- UNESCO checkbox/toggle.
- Clear filters action.

Search behavior:

- Searches object names using fuzzy search.
- Applies within active filters.
- Updates results immediately.

Map:

- Shows point markers for objects.
- Shows simplified polygons for area objects.
- Pin click opens popup.
- Popup contains photo, title, category/location context and "View Object".

Results grid cards:

- Main image.
- Title.
- Short description.
- Voivodeship.
- Category.
- UNESCO badge if applicable.
- Distance if geolocation/nearby mode is active.
- Optional compact indicators for hours, tickets, website if data exists.

States:

- Loading: skeleton for map and cards.
- Empty: message, active filters visible, "Clear Filters".
- Error: map/list loading error with retry.
- Populated: map and grid synchronized.

### Object Detail View

Tone: document-like, reference-oriented, readable and printable.

Components:

- Back link to catalog.
- Title.
- Metadata row: voivodeship, category, UNESCO.
- Main image.
- Gallery if multiple images exist.
- Description.
- Practical info below description:
  - Opening hours.
  - Ticket prices.
  - Website.
- Print button.
- Nearby objects card grid.

Print version:

- Include title, metadata, main image, description and practical info.
- Remove header navigation, filter controls, interactive map UI and footer noise.
- Keep layout readable on A4.

### News Listing

Purpose: simple editorial support layer for catalog discovery.

Layout:

- Page title: "News".
- Short intro.
- Article card grid.
- Optional latest objects row if needed by PRD.

Article card:

- Optional cover image.
- Title.
- Publication date.
- Short excerpt.
- Link to article.

States:

- Empty: "No published news articles".
- Loading: card skeletons.
- Error: retry.

### Article Detail

Layout:

- Back link to News.
- Title.
- Publication date.
- Optional cover image.
- Markdown-rendered body.
- Contextual CTA:
  - "Show Map"
  - "Browse Catalog"
  - or a filtered catalog link based on article content.

Article pages do not automatically show nearby objects unless an article is explicitly associated with a specific object in a future extension.

### CMS Login

Components:

- Login field.
- Password field.
- Submit button.
- Error message for invalid credentials.

States:

- Idle.
- Submitting.
- Invalid credentials.
- Authenticated redirect.

### CMS Dashboard

Simple two-section structure:

- Objects.
- News.

Each section should show:

- Total records.
- Published/unpublished counts.
- Primary add action.

### CMS Object List

Columns:

- Thumbnail.
- Title.
- Voivodeship.
- Category.
- Status: published/unpublished.
- Last updated.
- Actions: edit, publish/unpublish.

Optional destructive delete can be hidden or reserved for admin-only use. Primary removal action is unpublish.

### CMS Object Form

Fields:

- Title, required.
- Description, required.
- Images, minimum one required.
- Image ordering, first image is primary.
- Voivodeship.
- Category accordion/select with up to 3 levels.
- UNESCO boolean.
- Coordinates, required for map placement.
- Optional GeoJSON-style geometry input for simplified polygons.
- Opening hours.
- Ticket prices.
- Website.
- Publication status.

Validation:

- Required title.
- Required description.
- At least one image.
- Valid coordinate format.
- Valid URL if website is provided.
- Valid prepared geometry if polygon field is used.

### CMS Article List

Columns:

- Cover thumbnail if exists.
- Title.
- Publication date.
- Status.
- Last updated.
- Actions: edit, publish/unpublish.

### CMS Article Form

Fields:

- Title, required.
- Publication date, required.
- Cover image, optional.
- Markdown body, required.
- Publication status.
- Optional contextual CTA configuration:
  - Label.
  - URL or internal route.

Markdown editor should support preview if feasible.

---

## 4. Interaction Patterns

### Input & Control Behaviors

Filters:

- Immediate application on select/check.
- Active filters represented as chips.
- Chip removal updates map and results immediately.

Category accordion:

- Supports up to 3 levels.
- Parent categories expand/collapse.
- Selecting a parent may include all descendants unless technical data model requires exact category matching.
- Selected item remains visible after refresh/update.

Search:

- Debounced input.
- Fuzzy matching against object names.
- Runs within current filters.
- Empty query returns filtered result set.

Map:

- Pin click opens popup.
- Popup link opens object page.
- Polygon click can open same popup pattern if geometry represents a single object.
- Hover/focus on card may highlight corresponding pin on desktop.

Mobile filters:

- Filter button opens bottom sheet.
- Sheet contains the same controls as desktop.
- Changes apply immediately.
- Sheet has close affordance and supports escape/back behavior.

### Feedback Mechanisms

- Show result count after every filter/search change.
- Show loading skeletons for first load.
- Use non-blocking loading indicators for filter updates.
- Show clear empty states with recovery actions.
- Show validation errors inline in CMS forms.
- Show save success/toast in CMS.

### Transition Animations & Effects

Keep motion subtle:

- Bottom sheet slides from bottom on mobile.
- Filter chips appear/disappear with short fade.
- Map popup opens without heavy animation.
- CMS success/error toasts fade in/out.

Respect reduced motion preferences.

### Micro-interactions & UI Responses

- Object card hover: slight border/elevation change and map pin highlight on desktop.
- Focus states must be visible for keyboard users.
- Print button should invoke browser print.
- Publish/unpublish actions should show confirmation when changing from published to unpublished.

### Gesture Support

Mobile:

- Map uses native pan/zoom gestures.
- Bottom sheet should allow drag-to-dismiss only if accessible controls remain available.
- Segmented control switches between Map and List.

---

## 5. Design System Integration

### Component Usage Guidelines

Core components:

- Header/navigation.
- Footer.
- Button.
- Icon button.
- Text input.
- Select/listbox.
- Checkbox/toggle.
- Accordion.
- Badge.
- Card.
- Map popup.
- Bottom sheet.
- Modal/confirmation dialog.
- Toast.
- Skeleton.
- Markdown content renderer.

### Layout Grid Structure

Desktop catalog:

```text
Sidebar: 280-340px
Main content: remaining width
Main content max width: avoid overly wide cards below map
```

Object/detail pages:

- Document-like centered content.
- Comfortable reading width for description.
- Gallery and nearby object grid can use wider content area.

Homepage:

- Full-width sections with constrained inner content.
- Avoid nested cards.
- Use cards only for repeated objects/articles.

### Spacing Principles

- Use consistent vertical rhythm between page sections.
- Keep filter controls dense but readable.
- Object detail page should prioritize reading comfort.
- Cards should have consistent image ratio, title placement and metadata rhythm.

### UI Pattern Consistency

- Object cards and article cards should feel related but not identical.
- Object cards prioritize place/photo/location/category.
- Article cards prioritize date/title/excerpt.
- Badges should be reused for UNESCO/status/category metadata.

---

## 6. Accessibility Considerations

Target: WCAG 2.1 AA.

### Keyboard Navigation Paths

- Header navigation fully keyboard accessible.
- Filter controls reachable and operable by keyboard.
- Category accordion supports keyboard expand/collapse.
- Map must not trap keyboard focus.
- Map popup content reachable after opening.
- Bottom sheet traps focus while open and returns focus to trigger on close.
- CMS forms follow logical tab order.

### Screen Reader Experience

- Search input has clear label.
- Filter groups use accessible names.
- Result count changes should be announced politely.
- Map should have a text alternative through the results list.
- Object cards have descriptive link text.
- Form validation errors are associated with fields.
- Publish status is conveyed as text, not only color.

### Touch Target Guidelines

- Minimum interactive target size: 44 x 44 px.
- Filter sheet controls should be comfortable on mobile.
- Map popup links/buttons must be easy to tap.

### Color Contrast Requirements

- Text/background contrast meets WCAG AA.
- Badges and status indicators must not rely only on color.
- Focus states have strong visible contrast.

### Focus State Management

- Preserve focus after filter changes where possible.
- After opening bottom sheet, focus first meaningful control or sheet heading.
- After closing popup/sheet/modal, return focus to triggering element.
- After CMS save, keep user on form with success feedback unless workflow explicitly returns to list.

### Print Accessibility

- Printed object page should preserve meaningful content order.
- Hide navigation and interactive controls in print CSS.
- Keep text black or high contrast.
- Ensure image does not consume excessive page height.

---

## 7. Technical Implementation Notes

### Frontend Component Mapping

Suggested public components:

- `PublicHeader`
- `PublicFooter`
- `HomepageIntro`
- `LatestObjectsRow`
- `ArticleTeaserGrid`
- `CatalogLayout`
- `FilterSidebar`
- `MobileFilterSheet`
- `SearchBar`
- `ActiveFilterChips`
- `CatalogMap`
- `MapPopup`
- `ObjectCard`
- `ObjectGrid`
- `ObjectDetail`
- `ImageGallery`
- `PracticalInfo`
- `NearbyObjectsGrid`
- `ArticleGrid`
- `ArticleCard`
- `ArticleDetail`
- `MarkdownContent`

Suggested CMS components:

- `CmsLoginForm`
- `CmsLayout`
- `CmsDashboard`
- `ObjectTable`
- `ObjectForm`
- `ImageManager`
- `CoordinatesInput`
- `GeometryInput`
- `ArticleTable`
- `ArticleForm`
- `MarkdownEditor`
- `PublishStatusControl`

### View State Management Approach

Catalog state:

- Query.
- Selected voivodeship.
- Selected category.
- UNESCO filter.
- Active view on mobile: map/list.
- Selected map object for popup.
- Result collection.
- Loading/error states.

Recommended URL behavior:

- Persist search and filters in query params.
- This allows shareable catalog states and browser navigation.

Example:

```text
/katalog?q=castle&wojewodztwo=malopolskie&category=landmarks&unesco=true
```

CMS form state:

- Track dirty state.
- Warn before leaving unsaved changes.
- Validate on submit and inline where useful.

### Critical Rendering Considerations

- Map should have stable height before data loads to avoid layout shift.
- Card images should use consistent aspect ratios.
- Use responsive image sizes for object and article cards.
- Lazy-load below-the-fold card images.
- Keep map and results synchronized without full-page reloads.
- Ensure no public links expose unpublished objects or articles.

### Performance Optimization Suggestions

- Debounce search input.
- Avoid re-rendering the entire map on every minor UI update if possible.
- Cluster map markers if result volume grows.
- Cache latest objects for homepage.
- Cache article list where appropriate.
- Use pagination or "load more" for large result sets.

### Data and Content Assumptions

- First image in object media order is the primary image.
- Additional object images form a gallery.
- Coordinates are entered manually by editor and validated.
- Area geometry is prepared outside the CMS and entered/uploaded as valid structured data, preferably GeoJSON.
- Article body is Markdown.
- Article CTA can be configured per article or selected from common defaults.
- Unpublish is the primary safe removal action in CMS.

### Beta Scope Guardrails

- Do not add public user accounts.
- Do not add favorites, reviews, ratings or comments.
- Do not expand News into a full publishing platform.
- Do not add complex CMS workflow.
- Keep catalog, map and object detail pages as the main product value.
