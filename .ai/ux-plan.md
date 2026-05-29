# UX & UI Specification — Sightseeing Objects Catalog of Poland

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "object type" = PRD catalog classification; "news" = *aktualności*; "news listing" = *lista wpisów*

This document translates the PRD and design decisions into a UX/UI specification ready for direct use by a frontend developer or interface generation tool.

The scope covers a public web application, a map-first catalog, object pages, a homepage, a News section, and a simple CMS for the editorial team.

All production UI copy for the MVP must be in Polish. English labels and examples in this document are descriptive only.

---

## 1. Information Architecture

### Screen/Page Map with Hierarchy

```text
Public
/
  Homepage

/katalog
  Object catalog with map, filters, and result list

/katalog/[slug]
  Object detail page

/aktualnosci
  News listing page

/aktualnosci/[slug]
  News detail page

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

/cms/typy-obiektow
  Manage object type taxonomy

/cms/aktualnosci
  News listing

/cms/aktualnosci/new
  Add news entry

/cms/aktualnosci/[id]
  Edit news entry

/cms/uzytkownicy
  User and role management
```

### Content Grouping & Component Organization

The public application should have three main areas:

- **Object Discovery:** catalog, map, search, filters, detail pages.
- **Project Context:** homepage, purpose description, value for tourists and teachers.
- **Editorial Content:** News, newly added objects, entry points to catalog/map.

The CMS should remain simple and operational:

- **Objects:** list, add, edit, draft/published status, media, location.
- **Object Types:** editable taxonomy used by public filters and object forms.
- **News:** list, add, edit, publish/archive, featured flag.
- **Users:** administrator-managed editorial accounts and roles.

### Navigation Structure & Patterns

Main public navigation:

- Logo / project name
- Map
- Catalog
- News

Preferred model:

- CTA on the homepage leads primarily to the map/catalog view.
- "Map" link may lead to `/katalog` with the map as the active entry point.
- "Catalog" link leads to the same view, but oriented toward filtering and results.
- Public navigation labels and CTA copy use Polish in the implemented product.

CMS:

- Sidebar or top navigation with sections: Objects, Object Types, News.
- Administrator-only user management section for creating editorial users and assigning administrator/editor roles.
- Visible publication status for each record.
- Object statuses are draft and published.
- News supports draft, published, archived, and featured state.
- Delete actions are visible only to administrators; editors can create and edit but cannot delete.

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

[Dynamic Statistics]
  Number of published objects and object types

[Latest Objects]
  Row of 3-4 cards

[News teaser]
  Latest published news or entry to News section

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
[Metadata row: voivodeship | locality | object type | UNESCO badge]
[Main photo]
[Gallery thumbnails if more images exist]
[Lead / short description]
[Full description]
[Small location map: point or polygon]
[Practical info]
[Data source and last update]
[Up to 3 nearest published objects within 20 km card grid]
[Similar objects by type]
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
5. Narrows results by voivodeship, object type, or UNESCO.
6. Results update immediately on the map and card grid.
7. User clicks a pin on the map.
8. Sees a popup with image, title, and "View Object" link.
9. Navigates to the object detail page.
10. Reads the description, location map, practical info, data source, last update date, up to 3 nearest published objects within 20 km, and similar objects.

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
- Object type: accordion/tree up to 3 levels.
- UNESCO: toggle/checkbox.
- Clear filters: restores the full result set.

Filters apply immediately on change, without an "Apply" button.

### Nearby Objects Flow

On the object detail page:

1. System knows the location of the currently displayed object.
2. For point objects, nearest objects are calculated from the point coordinates.
3. For polygon objects, nearest objects are calculated from the polygon centroid.
4. System returns up to 3 geographically nearest published objects within a 20 km radius.
5. If fewer than 3 objects are available within 20 km, only the available objects are shown.
6. If no objects are available within 20 km, the section is omitted or replaced with a compact empty state.
7. Results are shown as a card grid.

### News Flow

1. User selects "News".
2. Sees a single public news page divided into sections for news/events and newly added objects.
3. Opens a news item.
4. Reads the published news entry.

### CMS Editorial Flow: Object

1. Editor logs in to the CMS.
2. Selects "Objects".
3. Sees a list of objects with publication status.
4. Adds or edits an object.
5. Fills in required fields: title, lead, full description, minimum one photo, object type, voivodeship, locality, and geometry.
6. Adds point coordinates or polygon geometry.
7. Optionally adds practical data, multiple photos, UNESCO designation, accessibility info, image author/source if known, data source, and last update date.
8. Saves.
9. After publishing, the object appears in the catalog, on the map, and in search results.

### CMS Editorial Flow: News

1. Editor selects "News".
2. Creates a news entry.
3. Fills in title, publication date, and content.
4. Optionally adds a cover photo.
5. Saves as draft or publishes.
6. Published entries appear on the News listing.
7. Archived entries are hidden from the public listing.
8. Featured entries receive visual priority where listing design supports it.

### CMS Editorial Flow: Users and Roles

1. Administrator selects "Users".
2. Sees editorial accounts with assigned roles.
3. Creates a new user or edits an existing user.
4. Assigns administrator or editor role.
5. Saves changes.
6. Updated permissions apply to CMS access and destructive actions.

### Decision Points & UI Branches

- No results after filtering: show empty state with option to clear filters.
- No nearest objects available: show a message and link to catalog/map.
- Fewer than 3 nearby objects within 20 km: show only the available published objects.
- No similar objects available: omit the similar objects section or show a compact empty state.
- No additional photo: don't show gallery, use only the main photo.
- Draft object: does not appear publicly.
- Draft or archived news entry: does not appear publicly.

### Flow Diagram Description for Mermaid

```text
Homepage --> CatalogMap
CatalogMap --> ApplyFilters
ApplyFilters --> UpdatedMapAndGrid
UpdatedMapAndGrid --> PinPopup
PinPopup --> ObjectDetail
UpdatedMapAndGrid --> ObjectCard
ObjectCard --> ObjectDetail
ObjectDetail --> NearbyObjects
ObjectDetail --> SimilarObjects

Homepage --> NewsList
NewsList --> NewsEventsSection
NewsList --> NewlyAddedObjectsSection
NewsEventsSection --> NewsDetail
```

---

## 3. View Specifications

### Homepage

Purpose: explain the project and route users into discovery.

Primary layout:

- Informational intro, not a marketing-heavy landing page.
- Primary CTA: "Show Map".
- Secondary CTA: "Browse Catalog".
- Dynamic statistics: published object count and object type count.
- Latest objects row: 3-4 cards.
- Latest news teaser.

Homepage object cards:

- Image
- Title
- Short location/object type context
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
- Object type accordion/tree up to 3 levels.
- UNESCO checkbox/toggle.
- Clear filters action.

Search behavior:

- Searches object names and matching text using partial phrase matching.
- Applies within active filters.
- Updates results immediately.

Map:

- Shows point markers for objects.
- Shows full polygons for area objects.
- Uses marker clustering for large result sets.
- Pin or polygon click opens popup.
- Popup contains photo, title, object type/location context and "View Object".

Results grid cards:

- Main image.
- Title.
- Short description.
- Voivodeship.
- Object type.
- UNESCO badge if applicable.
- Optional compact indicators for hours, tickets, and accessibility if data exists.

States:

- Loading: skeleton for map and cards.
- Empty: message, active filters visible, "Clear Filters".
- Error: map/list loading error with retry.
- Populated: map and grid synchronized.

### Object Detail View

Tone: document-like, reference-oriented, and readable.

Components:

- Back link to catalog.
- Title.
- Metadata row: voivodeship, locality, object type, UNESCO if applicable.
- Main image.
- Gallery if multiple images exist.
- Lead / short description.
- Full description.
- Small location map showing the point or polygon; polygon detail pages should fit the viewport to the full area.
- Practical info below description:
  - Opening hours.
  - Ticket prices.
  - Accessibility.
- Data source.
- Last update date.
- Up to 3 geographically nearest published objects within 20 km card grid.
- Similar objects by type card grid.

Print support is optional and should be treated as a future enhancement, not MVP scope.

### News Listing

Purpose: simple editorial support layer for catalog discovery.

Layout:

- Page title: "News".
- Short intro.
- Section 1: news/events card grid.
- Section 2: newly added objects card grid/list.

News card:

- Optional cover image.
- Title.
- Publication date.
- Short excerpt.
- Link to news detail.

States:

- Empty news/events section: "No published news entries".
- Empty newly added objects section: "No newly added objects".
- Loading: card skeletons.
- Error: retry.

### News Detail

Layout:

- Back link to News.
- Title.
- Publication date.
- Optional cover image.
- Rendered body content.

News detail pages do not automatically show nearby objects unless a future extension explicitly associates the entry with a specific object.

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

Simple four-section structure:

- Objects.
- Object Types.
- News.
- Users.

Each section should show:

- Total records.
- Status counts where applicable.
- Primary add/manage action.

### CMS Object List

Columns:

- Thumbnail.
- Title.
- Voivodeship.
- Object type.
- Status: draft/published.
- Author.
- Last updated.
- Actions: edit, publish, move to draft.
- Delete action: administrator only.

Editors can create and edit objects but cannot delete them.

### CMS Object Form

Fields:

- Title, required.
- Lead / short description, required.
- Full description, required.
- Images, minimum one required.
- Image ordering, selected main image.
- Optional image author and source fields, filled only if known.
- Voivodeship, required.
- Locality, required.
- Object type accordion/select with up to 3 levels, required.
- UNESCO boolean.
- Geometry type: point or polygon.
- Coordinates for point objects.
- GeoJSON-style geometry input/upload for polygon objects.
- Opening hours.
- Ticket prices.
- Accessibility.
- Data source.
- Last update date.
- Publication status: draft or published.
- Author assignment.

Validation:

- Required title.
- Required lead.
- Required full description.
- Required voivodeship, locality, object type, and geometry type.
- At least one image.
- Valid coordinate format for point objects.
- Valid prepared geometry for polygon objects.

### CMS Object Type Taxonomy

Purpose: allow editors/admins to maintain the object type taxonomy used in catalog filters and object forms.

List columns:

- Name.
- Parent object type if nested.
- Number of assigned objects.
- Status/visibility if supported.
- Actions: add, edit, reorder; delete only for administrators and only when safe.

### CMS News List

Columns:

- Cover thumbnail if exists.
- Title.
- Publication date.
- Status: draft/published/archived.
- Featured flag.
- Author.
- Last updated.
- Actions: edit, publish, archive, feature/unfeature.
- Delete action: administrator only.

### CMS News Form

Fields:

- Title, required.
- Publication date, required.
- Cover image, optional.
- Content body, required.
- Publication status: draft, published, or archived.
- Featured flag.
- Author assignment.

### CMS User List and Form

Purpose: allow administrators to manage editorial accounts and assign roles.

List columns:

- Name.
- Email/login.
- Role: administrator/editor.
- Status if supported.
- Last updated.
- Actions: edit; delete only for administrators and only when safe.

Form fields:

- Name, required.
- Email/login, required.
- Role, required.
- Password fields when creating or resetting credentials.

Editors do not have access to this area.

---

## 4. Interaction Patterns

### Input & Control Behaviors

Filters:

- Immediate application on select/check.
- Active filters represented as chips.
- Chip removal updates map and results immediately.

Object type accordion:

- Supports up to 3 levels.
- Parent object types expand/collapse.
- Selecting a parent may include all descendants unless technical data model requires exact object type matching.
- Selected item remains visible after refresh/update.

Search:

- Debounced input.
- Partial phrase matching against object names and relevant text fields.
- Runs within current filters.
- Empty query returns filtered result set.

Map:

- Pin click opens popup.
- Popup link opens object page.
- Polygon click opens the same popup pattern for the represented object.
- Marker clustering is enabled for large result sets.
- Object detail map fits the viewport to the full polygon for polygon objects.
- Hover/focus on card may highlight corresponding pin or polygon on desktop.

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
- Moving a published object back to draft should show confirmation.
- Archiving a published news entry should show confirmation.

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
- Rich text content renderer.

### Layout Grid Structure

Desktop catalog:

```text
Sidebar: 280-340px
Main content: remaining width
Main content max width: avoid overly wide cards below map
```

Object/detail pages:

- Document-like centered content.
- Comfortable reading width for lead and full description.
- Gallery, small map, nearest objects, and similar objects can use wider content area.

Homepage:

- Full-width sections with constrained inner content.
- Avoid nested cards.
- Use cards only for repeated objects/news entries.

### Spacing Principles

- Use consistent vertical rhythm between page sections.
- Keep filter controls dense but readable.
- Object detail page should prioritize reading comfort.
- Cards should have consistent image ratio, title placement and metadata rhythm.

### UI Pattern Consistency

- Object cards and news cards should feel related but not identical.
- Object cards prioritize place/photo/location/object type.
- News cards prioritize date/title/excerpt.
- Badges should be reused for UNESCO/status/object type metadata.

---

## 6. Accessibility Considerations

Target: WCAG 2.1 A.

### Keyboard Navigation Paths

- Header navigation fully keyboard accessible.
- Filter controls reachable and operable by keyboard.
- Object type accordion supports keyboard expand/collapse.
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
- Publication status is conveyed as text, not only color.

### Touch Target Guidelines

- Minimum interactive target size: 44 x 44 px.
- Filter sheet controls should be comfortable on mobile.
- Map popup links/buttons must be easy to tap.

### Color Contrast Requirements

- Text/background contrast should remain strong for readability even though MVP compliance target is WCAG A.
- Badges and status indicators must not rely only on color.
- Focus states have strong visible contrast.

### Focus State Management

- Preserve focus after filter changes where possible.
- After opening bottom sheet, focus first meaningful control or sheet heading.
- After closing popup/sheet/modal, return focus to triggering element.
- After CMS save, keep user on form with success feedback unless workflow explicitly returns to list.

### Future Print Accessibility

Print support is optional future scope. If implemented, the printed object page should preserve meaningful content order, hide navigation/interactive controls, keep text high contrast, and prevent images from consuming excessive page height.

---

## 7. Technical Implementation Notes

### Frontend Component Mapping

Suggested public components:

- `PublicHeader`
- `PublicFooter`
- `HomepageIntro`
- `HomepageStats`
- `LatestObjectsRow`
- `NewsEventsGrid`
- `NewlyAddedObjectsGrid`
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
- `ObjectLocationMap`
- `PracticalInfo`
- `NearestObjectsGrid`
- `SimilarObjectsGrid`
- `NewsGrid`
- `NewsCard`
- `NewsDetail`
- `RichTextContent`

Suggested CMS components:

- `CmsLoginForm`
- `CmsLayout`
- `CmsDashboard`
- `ObjectTable`
- `ObjectForm`
- `ImageManager`
- `CoordinatesInput`
- `GeometryInput`
- `ObjectTypeTable`
- `ObjectTypeForm`
- `NewsTable`
- `NewsForm`
- `UserTable`
- `UserForm`
- `PublishStatusControl`

### View State Management Approach

Catalog state:

- Query.
- Selected voivodeship.
- Selected object type.
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
/katalog?q=castle&wojewodztwo=malopolskie&type=landmarks&unesco=true
```

Search requests should use partial phrase matching semantics rather than fuzzy ranking.

CMS form state:

- Track dirty state.
- Warn before leaving unsaved changes.
- Validate on submit and inline where useful.

### Critical Rendering Considerations

- Map should have stable height before data loads to avoid layout shift.
- Card images should use consistent aspect ratios.
- Use responsive image sizes for object and news cards.
- Lazy-load below-the-fold card images.
- Keep map and results synchronized without full-page reloads.
- Ensure no public links expose draft objects or draft/archived news entries.
- Object and news pages should support SEO metadata: friendly URLs, page titles, meta descriptions, and social sharing metadata where content exists.

### KPI Event Instrumentation

Track at minimum:

- Homepage to catalog/map CTA clicks.
- Homepage to object detail navigations.
- Homepage to news navigations.
- Catalog search changes.
- Catalog filter changes for voivodeship, object type, and UNESCO.
- Map zooms and pans where technically feasible.
- Marker opens and polygon opens.
- Map popup opens.
- Catalog to object detail navigations.
- Object detail page views.
- News listing page views.
- News detail page views.
- News or homepage to object-detail navigations.

### Performance Optimization Suggestions

- Debounce search input.
- Avoid re-rendering the entire map on every minor UI update if possible.
- Cluster map markers for large result sets.
- Cache homepage statistics and latest objects.
- Cache news list where appropriate.
- Use pagination or "load more" for large result sets.

### Data and Content Assumptions

- Selected main image is used as the primary image; additional object images form a gallery.
- Image author/source fields are available when known; images are assumed to be PTTK-owned unless specified otherwise.
- Coordinates are entered manually by editor and validated for point objects.
- Area geometry is prepared outside the CMS and entered/uploaded as valid structured data, preferably GeoJSON.
- Nearest objects are up to 3 geographically nearest published objects within 20 km; polygon objects use centroid-based distance.
- If no nearby published objects exist within 20 km, the section is omitted or an empty state is shown.
- News entry body is stored as editorial content without requiring blog-platform features.
- Object status is draft or published.
- News status is draft, published, or archived, with a separate featured flag.
- Public user geolocation and print support are future enhancements, not MVP scope.

### Beta Scope Guardrails

- Do not add public user accounts.
- Do not add favorites, reviews, ratings or comments.
- Do not expand News into a full publishing platform.
- Do not add complex CMS workflow.
- Keep catalog, map and object detail pages as the main product value.
