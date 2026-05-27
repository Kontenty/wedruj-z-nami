# UX & UI Specification - Katalog Obiektów Krajoznawczych Polski

Dokument przekłada PRD oraz ustalenia projektowe na specyfikację UX/UI możliwą do bezpośredniego wykorzystania przez frontend developera lub narzędzie do generowania interfejsów.

Zakres obejmuje publiczną aplikację webową, katalog z mapą, strony obiektów, stronę główną, sekcję Aktualności oraz prosty CMS dla zespołu redakcyjnego.

---

## 1. Information Architecture

### Screen/Page Map with Hierarchy

```text
Public
/
  Strona główna

/katalog
  Katalog obiektów z mapą, filtrami i listą wyników

/obiekty/[slug]
  Strona szczegółowa obiektu

/aktualnosci
  Lista wpisów redakcyjnych

/aktualnosci/[slug]
  Szczegół wpisu

/kontakt
  Opcjonalna strona kontaktowa albo sekcja kontaktowa dostępna z footer/nav

CMS
/cms/login
  Logowanie redaktora

/cms
  Dashboard CMS

/cms/obiekty
  Lista obiektów

/cms/obiekty/nowy
  Dodawanie obiektu

/cms/obiekty/[id]
  Edycja obiektu

/cms/aktualnosci
  Lista wpisów

/cms/aktualnosci/nowy
  Dodawanie wpisu

/cms/aktualnosci/[id]
  Edycja wpisu
```

### Content Grouping & Component Organization

Publiczna aplikacja powinna mieć trzy główne obszary:

- **Odkrywanie obiektów:** katalog, mapa, wyszukiwarka, filtry, strony szczegółowe.
- **Kontekst projektu:** strona główna, opis celu, wartości dla turystów i nauczycieli.
- **Treści redakcyjne:** Aktualności, artykuły, wejścia do katalogu/mapy.

CMS powinien pozostać prosty i operacyjny:

- **Obiekty:** lista, dodawanie, edycja, publikacja/unpublish, media, lokalizacja.
- **Aktualności:** lista, dodawanie, edycja, publikacja/unpublish, Markdown.

### Navigation Structure & Patterns

Główna nawigacja publiczna:

- Logo/nazwa projektu
- Mapa
- Katalog
- Aktualności
- Kontakt

Preferowany model:

- CTA na stronie głównej prowadzi przede wszystkim do widoku mapy/katalogu.
- Link "Mapa" może prowadzić do `/katalog` z mapą jako aktywnym punktem wejścia.
- Link "Katalog" prowadzi do tego samego widoku, ale z nastawieniem na filtrowanie i wyniki.

CMS:

- Sidebar lub górna nawigacja z sekcjami: Obiekty, Aktualności.
- Widoczny status publikacji przy każdym rekordzie.
- Akcja "Cofnij publikację" jako bezpieczna alternatywa dla usuwania.

### Layout Zones & Content Blocks

Strona główna:

```text
[Header]
[Intro / hero informacyjny]
  Tytuł, krótki opis projektu
  Primary CTA: Pokaż mapę
  Secondary CTA: Przeglądaj katalog

[Dla kogo i po co]
  Turyści, nauczyciele, osoby planujące wyjazdy

[Najnowsze obiekty]
  Rząd 3-4 kart

[Aktualności teaser]
  Kilka ostatnich wpisów albo wejście do sekcji Aktualności

[Kontakt / feedback prompt]
[Footer]
```

Katalog desktop:

```text
[Header]

[Left filter sidebar]  [Main discovery area]
                       [Search]
                       [Active filter chips + result count]
                       [Map]
                       [Card grid results]
```

Katalog mobile:

```text
[Header]
[Search]
[Map | Lista segmented control] [Filtry]

Default: Map

Map view:
  [Full-width map]
  [Pin popup / bottom preview when selected]

List view:
  [Card grid/list adapted to one column]

Filters:
  [Bottom sheet]
```

Strona obiektu:

```text
[Header]
[Back to catalog]

[Title]
[Metadata row: województwo | kategoria | UNESCO badge]
[Main photo]
[Gallery thumbnails if more images exist]
[Description]
[Practical info]
[Print button]
[Nearby objects card grid]
[Footer]
```

### Responsive Behavior Guidelines

- Desktop: filtracja stale widoczna po lewej, mapa pierwsza w centralnym obszarze, wyniki poniżej.
- Tablet: filtry mogą przejść do panelu bocznego lub przycisku "Filtry", mapa pozostaje przed wynikami.
- Mobile: domyślnie mapa, przełącznik Map/List, filtry jako bottom sheet.
- Karty obiektów w mobile powinny być jednokolumnowe.
- Mapa na mobile musi mieć wystarczającą wysokość, aby była użyteczna, minimum około 50-60% wysokości viewportu w trybie mapy.

---

## 2. Core User Flows

### Primary User Journey: Planning a Visit

1. Użytkownik wchodzi na stronę główną.
2. Czyta krótki opis celu projektu.
3. Wybiera "Pokaż mapę".
4. Trafia do katalogu z widoczną mapą i filtrami.
5. Zawęża wyniki przez województwo, kategorię lub UNESCO.
6. Wyniki aktualizują się natychmiast na mapie i w siatce kart.
7. Użytkownik klika pin na mapie.
8. Widzi popup z obrazem, tytułem i linkiem "Zobacz obiekt".
9. Przechodzi na stronę obiektu.
10. Czyta opis, sprawdza informacje praktyczne i obiekty w pobliżu.
11. Opcjonalnie drukuje stronę obiektu.

### Search and Filter Flow

```text
User enters query
  -> Search applies within currently selected filters
  -> Result count updates
  -> Map markers/polygons update
  -> Active chips update
  -> Card grid updates
```

Filtry:

- Województwo: single select.
- Kategoria: accordion/tree do 3 poziomów.
- UNESCO: toggle/checkbox.
- Wyczyść filtry: przywraca pełny zestaw wyników.

Filtry działają natychmiast po zmianie, bez przycisku "Zastosuj".

### Nearby Objects Flow

Na stronie obiektu:

1. System zna lokalizację aktualnie wyświetlanego obiektu.
2. Pobiera obiekty w pobliżu.
3. Domyślny promień: 5 km.
4. Jeśli brak wyników, system rozszerza promień do 20 km.
5. Wyniki są pokazane jako card grid.

W trybie geolokalizacji użytkownika:

1. Użytkownik wybiera funkcję obiektów w pobliżu.
2. System prosi o dostęp do lokalizacji.
3. Po zgodzie pokazuje obiekty w promieniu 5 km.
4. Jeśli brak wyników, rozszerza do 20 km.
5. Jeśli użytkownik odmówi, pokazuje komunikat i alternatywę: użyj mapy lub filtrów.

### Article Flow

1. Użytkownik wybiera "Aktualności".
2. Widzi grid wpisów z datą, tytułem, opcjonalną okładką i skrótem.
3. Otwiera wpis.
4. Czyta treść Markdown wyrenderowaną jako artykuł.
5. Na końcu widzi kontekstowy CTA, np. "Pokaż mapę", "Przeglądaj katalog" albo link do filtrowanych obiektów.

### CMS Editorial Flow: Object

1. Redaktor loguje się do CMS.
2. Wybiera "Obiekty".
3. Widzi listę obiektów ze statusem publikacji.
4. Dodaje lub edytuje obiekt.
5. Wypełnia wymagane pola: tytuł, opis, minimum jedno zdjęcie.
6. Dodaje współrzędne.
7. Opcjonalnie dodaje dane praktyczne, wiele zdjęć, kategorię, UNESCO, geometrię obszaru.
8. Zapisuje.
9. Po publikacji obiekt pojawia się w katalogu, na mapie i w wynikach wyszukiwania.

### CMS Editorial Flow: Article

1. Redaktor wybiera "Aktualności".
2. Tworzy nowy wpis.
3. Wypełnia tytuł, datę publikacji i treść Markdown.
4. Opcjonalnie dodaje zdjęcie główne.
5. Zapisuje i publikuje.
6. Wpis pojawia się na liście Aktualności.

### Decision Points & UI Branches

- Brak wyników po filtrowaniu: pokaż empty state z możliwością wyczyszczenia filtrów.
- Brak wyników w pobliżu 5 km: automatyczne rozszerzenie do 20 km, z komunikatem.
- Brak wyników w pobliżu 20 km: pokaż komunikat i link do katalogu/mapy.
- Brak zdjęcia dodatkowego: nie pokazuj galerii, użyj tylko głównego zdjęcia.
- Obiekt nieopublikowany: nie pojawia się publicznie.
- Wpis nieopublikowany: nie pojawia się publicznie.
- Odmowa geolokalizacji: pokaż fallback oparty na mapie i filtrach.

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

Homepage --> AktualnosciList
AktualnosciList --> ArticleDetail
ArticleDetail --> ContextualCTA
ContextualCTA --> CatalogMap
```

---

## 3. View Specifications

### Homepage

Purpose: explain the project and route users into discovery.

Primary layout:

- Informational intro, not a marketing-heavy landing page.
- Primary CTA: "Pokaż mapę".
- Secondary CTA: "Przeglądaj katalog".
- Latest objects row: 3-4 cards.
- Aktualności teaser.
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

- Województwo select/list.
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
- Popup contains photo, title, category/location context and "Zobacz obiekt".

Results grid cards:

- Main image.
- Title.
- Short description.
- Województwo.
- Category.
- UNESCO badge if applicable.
- Distance if geolocation/nearby mode is active.
- Optional compact indicators for hours, tickets, website if data exists.

States:

- Loading: skeleton for map and cards.
- Empty: message, active filters visible, "Wyczyść filtry".
- Error: map/list loading error with retry.
- Populated: map and grid synchronized.

### Object Detail View

Tone: document-like, reference-oriented, readable and printable.

Components:

- Back link to catalog.
- Title.
- Metadata row: województwo, category, UNESCO.
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

### Aktualności List

Purpose: simple editorial support layer for catalog discovery.

Layout:

- Page title: "Aktualności".
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

- Empty: "Brak opublikowanych aktualności".
- Loading: card skeletons.
- Error: retry.

### Article Detail

Layout:

- Back link to Aktualności.
- Title.
- Publication date.
- Optional cover image.
- Markdown-rendered body.
- Contextual CTA:
  - "Pokaż mapę"
  - "Przeglądaj katalog"
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

- Obiekty.
- Aktualności.

Each section should show:

- Total records.
- Published/unpublished counts.
- Primary add action.

### CMS Object List

Columns:

- Thumbnail.
- Title.
- Województwo.
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
- Województwo.
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
- Segmented control switches between Map and Lista.

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
- Selected województwo.
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
/katalog?q=zamek&wojewodztwo=malopolskie&kategoria=zabytki&unesco=true
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
- Do not expand Aktualności into a full publishing platform.
- Do not add complex CMS workflow.
- Keep catalog, map and object detail pages as the main product value.
