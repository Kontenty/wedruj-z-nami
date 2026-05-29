Kanon PRD

Tuesday, August 26, 2025
20:52

# Product Requirements Document (PRD) — Nationwide PTTK Sightseeing Objects Catalog

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*

## 1. General Information
**Project name:** Nationwide PTTK Sightseeing Objects Catalog  
**Product owner:** Polish Tourist and Sightseeing Society (PTTK)  
**Project goal:** Build a public, editorial web service presenting sightseeing objects in Poland, integrated with a map, and enabling browsing, filtering, and viewing detailed object information created and maintained by PTTK.

## 2. Business and Content Goals
- Provide a credible, comprehensive catalog of sightseeing objects in Poland.
- Promote cultural, historical, and natural heritage.
- Support PTTK statutory activity.
- Strengthen PTTK recognition as a trusted sightseeing knowledge source.
- Build a structured data foundation for future product development.

## 3. User Groups
### 3.1 Public users (no login)
- Individual tourists
- PTTK members
- Students and teachers
- Tour guides
- People planning trips

### 3.2 Editorial users (authenticated)
- Central PTTK administrators
- Editors (e.g., PTTK staff and regional branches)

## 4. Catalog Content Scope
The service covers sightseeing objects in Poland, classified by types such as:
- Architectural monuments
- Museums and open-air museums
- Religious sites
- Monuments and memorial places
- Natural sites
- Fortifications and castles
- Industrial/technical heritage sites
- Historical places

Each object is created and maintained editorially by PTTK. Objects may also be marked as UNESCO-designated where applicable.

## 5. Functional Requirements — Public Application
### 5.1 Homepage
- Project and mission overview.
- Dynamic statistics (e.g., number of objects, categories/types).
- Latest objects list.
- Latest news list.
- Clear links to catalog and map.

### 5.2 Objects Catalog and Map
- Interactive map of Poland.
- Point markers for point-based objects.
- Polygons for area-based objects (e.g., national parks).
- Map popup with basic object details.
- Object list synchronized with the map.
- Filtering by:
  - object type,
  - voivodeship,
  - text search,
  - UNESCO designation.
- Marker clustering for large datasets.

### 5.3 Object Detail Page
- Object name, type, and UNESCO designation if applicable.
- Main image and image gallery.
- Short description (lead) and full description.
- Location:
  - map (point or polygon),
  - administrative data (voivodeship, locality).
- Optional practical information:
  - opening hours,
  - ticket prices,
  - accessibility.
- List of up to 3 geographically nearest published objects within a 20 km radius.
- List of similar objects (by type).
- Data source and last update date.

### 5.4 News
- Single public news page with sections:
  - news/events,
  - newly added objects.
- News detail page.

## 6. Geolocation Requirements
Each object has a geometry type:
- **Point** (default; most objects)
- **Polygon** (for larger areas, e.g., parks, reserves)

For nearby object calculation:
- Show up to 3 geographically nearest published objects within a 20 km radius.
- If fewer than 3 objects exist within 20 km, show only the available objects.
- If no objects exist within 20 km, do not show the nearby objects list or show an empty state.
- For polygon objects, calculate nearest objects using the polygon centroid.

For polygons:
- Display the full area on map views.
- Fit map viewport on the object detail page.

## 7. Functional Requirements — CMS (Editorial Panel)
### 7.1 Objects Management
- Create and edit objects.
- Delete objects (administrator only).
- Support both points and polygons.
- Assign object types and voivodeships.
- Mark UNESCO designation where applicable.
- Object type taxonomy is editable in CMS.
- Add image gallery.
- Publication statuses:
  - draft,
  - published.

### 7.2 News Management
- Create and edit news entries.
- Delete news entries (administrator only).
- Publication statuses/actions:
  - draft,
  - published,
  - archived.
- Mark selected entries as featured.

### 7.3 Users and Roles
- Roles:
  - administrator,
  - editor.
- Administrator has full permissions, including delete operations.
- Editor can create and edit content but cannot delete.
- Author assignment for content.

### 7.4 Media
- Image upload.
- Store image author and source if known; images are assumed to be PTTK-owned unless specified otherwise.
- Set a main image.

## 8. Non-Functional Requirements
- Responsive UX (desktop, tablet, mobile).
- Intuitive CMS for non-technical editors.
- High map performance with large numbers of objects.
- Extensible architecture for future features.
- WCAG compliance at level A.
- SEO-ready structure (friendly URLs, metadata support).
- Product language for MVP: Polish only.

## 9. MVP Scope (Version 1)
- Homepage.
- Objects catalog with map and filters.
- Object detail page.
- News list and news detail page.
- CMS for objects and news.
- Administrator and editor roles.
- Support for point and polygon geometries.

## 10. Product Boundaries / Out of Scope
The following features are explicitly excluded from MVP scope:
- End-user accounts, login, registration, and user profiles for public users.
- Personalization features based on user preferences or browsing history.
- Saving favorite objects, creating routes, or trip-planning functionality.
- Public ratings, reviews, and comments.
- Multilingual content or interface versions; MVP is Polish-only.
- Advanced news/blog publishing features, including tags, comments, public author profiles, scheduled publishing, newsletters, recommendations, and content personalization.
- Expanding the news/blog section into a full publishing platform; news remains a lightweight editorial layer supporting the catalog.

## 11. User Stories
### End User
**US-001 — Browse objects catalog**  
As a tourist, I want to browse all objects so I can discover attractions.  
**Acceptance criteria:**
- The system shows an object list with title, short description, and image.
- The list contains all published objects.

**US-002 — Filter by voivodeship**  
As a tourist, I want to filter objects by voivodeship so I can narrow results to a region.  
**Acceptance criteria:**
- User can select a voivodeship.
- The system shows only matching objects.

**US-003 — Filter by object type**  
As a tourist, I want to filter by type so I can find places relevant to my interests.  
**Acceptance criteria:**
- Type filter is available in catalog view.
- Results update according to selected type.

**US-003A — Filter by UNESCO designation**  
As a tourist, I want to filter UNESCO-designated objects so I can find places with this designation.  
**Acceptance criteria:**
- UNESCO filter is available in catalog view.
- Results update according to the selected UNESCO filter.

**US-004 — Search by name/text**  
As a tourist, I want to search objects by text so I can quickly find a place.  
**Acceptance criteria:**
- Search supports partial phrase matching.
- Results include matching objects.

**US-005 — Explore map**  
As a tourist, I want to see objects on a map so I can understand their location context.  
**Acceptance criteria:**
- Map shows object points and area polygons.
- Clicking marker/polygon opens object preview popup.

**US-006 — View object details**  
As a tourist, I want to open an object page so I can read detailed information.  
**Acceptance criteria:**
- Page includes title, type, lead, full description, and main image.
- Optional practical data is shown when available.
- Page shows up to 3 nearest published objects within 20 km and similar objects.

**US-007 — Read news**  
As a visitor, I want to browse news so I can stay up to date.  
**Acceptance criteria:**
- A single public news page is available with sections for news/events and newly added objects.
- User can open a dedicated news detail page.

### Editorial Team
**US-008 — Add object**  
As an editor, I want to add an object so the catalog grows over time.  
**Acceptance criteria:**
- Form supports required object fields and geometry.
- Validation prevents incomplete required data.
- Saved object is available according to publication status.

**US-009 — Edit object**  
As an editor, I want to edit object content so information stays current.  
**Acceptance criteria:**
- Editor can modify all object fields.
- Changes are visible after save and publication.

**US-010 — Manage news entry**  
As an editor, I want to create and edit news so I can publish updates.  
**Acceptance criteria:**
- News form supports title, publication date, and content.
- Entry can be saved as draft, published, archived, and marked/unmarked as featured.

### Security and Access
**US-011 — Secure CMS access**  
As an editorial user, I want authenticated access so CMS data is protected.  
**Acceptance criteria:**
- CMS requires authentication.
- Access is role-based (administrator/editor).
- Unauthenticated users cannot access CMS.

## 12. Success Metrics (KPI)
- Number of active users.
- Number of catalog searches and filter interactions.
- Average time on object detail pages.
- Number of map interactions (zoom, marker/polygon opens, popup opens).
- Number of news page views and average time on news pages.
- Number of navigations from homepage/news to object pages.
- Database consistency: 100% of published objects have required core fields.
- Accessibility compliance target: WCAG level A.
- Delivery of beta scope within planned timeline.
