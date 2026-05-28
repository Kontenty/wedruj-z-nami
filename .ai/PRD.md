Kanon PRD

Tuesday, August 26, 2025
20:52

# Product Requirements Document (PRD) — Sightseeing Objects Catalog of Poland

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*

## 1. Product Overview
The goal of the project is to create an open web application presenting a catalog of Polish sightseeing objects (*obiekty krajoznawcze*), such as historic landmarks, national parks, and nature reserves. Users will be able to filter objects by voivodeship (*województwo*), category, and UNESCO status, search them by name, and browse them on a map. Each object page will contain basic information (title, description, photo) and optional practical data. Additionally, users will see other objects located nearby.

The application will also include:
- a public homepage presenting the project, its purpose, and the main sections of the service,
- a blog section with news and articles,
- a module showcasing the latest objects on the homepage and blog page.

The system will enable a non-technical team to easily edit content through a simple CMS. The CMS will handle both sightseeing objects and blog posts. The blog is intended to serve a supporting role for the catalog by providing news, context, and additional editorial content, without transforming the product into an extensive publishing platform. The project is being carried out by a team consisting of 1 developer + 1 designer, with a planned beta delivery time of 5 months.

## 2. User Problem
In Poland, there is no centralized, tourist- and teacher-friendly catalog of sightseeing objects. Currently, information is scattered across many sources, often inconsistent or difficult to access. The application aims to solve this problem by offering:
- an organized database of sightseeing objects in one place,
- easy filtering and searching capabilities,
- geographic context (map, nearby objects),
- access to basic practical information in a clear format,
- the ability to print an object page as educational or travel material,
- a clear entry point to the project via the homepage,
- news and articles supporting the discovery of new objects and building user engagement.

## 3. Functional Requirements
1. Sightseeing objects catalog:
   - Each object contains a title, description, and min. one photo (required fields).
   - Optional data: opening hours, ticket prices, website.
   - Hierarchical categories up to 3 levels deep.
   - UNESCO attribute as an additional filter.
   - Each object has an addition or publication date used to determine the list of latest objects.
2. Filters and search:
   - Filter by voivodeship (*województwo*).
   - Filter by category.
   - Filter by UNESCO status.
   - Object search by name (fuzzy search).
3. Nearby objects:
   - Geolocation function with a default radius of 5 km.
   - If no results, the system searches within a 20 km radius.
4. Map:
   - Display of object points.
   - Display of simplified polygons for areas (e.g., parks).
5. Object detail page:
   - Title, description, photo (required).
   - Optional data (hours, prices, website).
   - List of nearby objects.
   - Page print option.
6. Homepage:
   - Public landing page containing a description of the project, its purpose, and value for the user.
   - Homepage contains clear entry points to the catalog, map, and blog.
   - Homepage presents a section of the latest objects with title, photo, and link to the detail page.
   - The informational content of the homepage can be managed statically in the first version, while the list of latest objects is fetched dynamically from the catalog.
7. Blog / news:
   - Public list of articles and news published in reverse chronological order.
   - Dedicated article detail page.
   - Blog post contains at minimum: title, publication date, and content.
   - Optionally, a post may contain a main/cover photo.
   - Blog page contains a latest objects section linked to the catalog.
   - Beta version does not include tags, comments, author profiles, publication scheduling, or related post recommendation mechanisms.
8. CMS:
   - Simple operation for non-technical users.
   - Editing and adding objects.
   - Editing, adding, and deleting blog posts.
   - Validation of required fields.
   - No approval workflow.
   - Shared media handling for objects and blog posts, if implemented.
9. Access:
   - Open application — no login for end users.
   - CMS secured (access only for the team).
10. Communication:
    - Contact form or email address for the team.
    - No review/rating system.
11. Accessibility:
    - WCAG compliance.
    - Printable pages.
    - Homepage and blog accessible on mobile and desktop devices.

## 4. Product Boundaries
- No login or personalization for end users.
- No ability to save favorite objects or routes.
- No rating and review system.
- No multilingualism (Polish language only).
- Data and photos come exclusively from the team's own database.
- Updates to practical data (e.g., opening hours) depend entirely on the team.
- No plan for production deployment in this phase — the goal is a beta version.
- Beta blog does not include advanced publishing features such as tagging, comments, author profiles, scheduled publishing, newsletters, or content personalization.
- The homepage serves an informational and navigational role, not an extensive marketing service.

## 5. User Stories

### End User
US-001
Title: Browsing the objects catalog
Description: As a tourist, I want to see a list of all objects so I can browse available attractions.
Acceptance Criteria:
- System displays a list of objects with title, short description, and photo.
- List contains all objects available in the database.

US-002
Title: Filtering by voivodeship
Description: As a tourist, I want to filter objects by voivodeship so I can narrow my search to a specific region.
Acceptance Criteria:
- User can select one voivodeship from a list.
- System displays only objects from the selected voivodeship.

US-003
Title: Filtering by category
Description: As a tourist, I want to filter objects by category so I can find types of objects that interest me.
Acceptance Criteria:
- Categories are presented in a hierarchy up to 3 levels.
- System filters objects according to the selected category.

US-004
Title: Filtering by UNESCO status
Description: As a tourist, I want to filter objects on the UNESCO list so I can find objects of special value.
Acceptance Criteria:
- "UNESCO" filter available as a separate option.
- System displays only objects marked with UNESCO status.

US-005
Title: Search by name
Description: As a tourist, I want to search for an object by name so I can quickly find a place of interest.
Acceptance Criteria:
- Search engine supports partial name matching (fuzzy search).
- Results contain objects matching the entered phrase.

US-006
Title: Nearby objects
Description: As a tourist, I want to see objects near my location so I can discover attractions in the area.
Acceptance Criteria:
- Default search radius: 5 km.
- If no results, the system automatically increases the radius to 20 km.
- Result list shows objects with distance.

US-007
Title: Map browsing
Description: As a tourist, I want to see objects on a map so I can better understand their location.
Acceptance Criteria:
- Map displays object location points.
- Areas (e.g., parks) are presented as simplified polygons.

US-008
Title: Object detail page
Description: As a tourist, I want to view the detailed object page so I can get more information.
Acceptance Criteria:
- Page contains: title, description, min. 1 photo.
- Page may contain optional information: opening hours, ticket prices, website.
- System displays a list of nearby objects.

US-009
Title: Printing the object page
Description: As a teacher, I want to print the object page so I can use it as educational material.
Acceptance Criteria:
- Object page has a "print" button.
- Print includes title, description, photo, and basic information.

US-015
Title: Understanding the project on the homepage
Description: As a visitor, I want to see a homepage explaining what the project is so I can quickly understand what the service offers.
Acceptance Criteria:
- Homepage contains a description of the project and its purpose.
- Homepage contains clear links to the catalog, map, and blog.
- Page works correctly on mobile and desktop devices.

US-016
Title: Browsing latest objects
Description: As a visitor, I want to see the latest objects so I can discover recently added places.
Acceptance Criteria:
- Homepage displays a list of latest objects.
- Each list item contains title, photo, and link to the object page.
- Object order is based on the addition or publication date in the catalog.

US-017
Title: Browsing news and articles
Description: As a visitor, I want to browse articles and news so I can stay up to date with new discoveries.
Acceptance Criteria:
- System displays a list of published articles in reverse chronological order.
- Each article on the list contains title, publication date, and excerpt or cover photo.
- User can navigate to the article detail page.

US-018
Title: Reading an article
Description: As a visitor, I want to open an article page so I can read the full news content.
Acceptance Criteria:
- Article page contains title, publication date, and full content.
- If the post contains a cover photo, it is visible on the article page.
- From the article page, the user can navigate to the blog or catalog.

### CMS (Editorial Team)
US-010
Title: Adding a new object
Description: As an editor, I want to add a new object to the catalog so the database is complete.
Acceptance Criteria:
- Object add form contains required fields: title, description, photo.
- System validates completion of required fields.
- Object appears in the catalog after saving.

US-011
Title: Editing an object
Description: As an editor, I want to edit an existing object so I can update practical data.
Acceptance Criteria:
- Edit form accessible from the CMS.
- Ability to edit all object fields.
- Changes are visible immediately after saving.

US-012
Title: Deleting an object
Description: As an editor, I want to delete an object so the database stays current and correct.
Acceptance Criteria:
- CMS enables permanent deletion of an object.
- After deletion, the object disappears from the catalog and map.

US-019
Title: Adding a blog post
Description: As an editor, I want to create and publish a blog post so I can communicate news and publish articles.
Acceptance Criteria:
- Post add form contains required fields: title, publication date, content.
- Cover photo remains an optional field.
- After saving and publishing, the post appears on the blog page.

US-020
Title: Editing a blog post
Description: As an editor, I want to edit a blog post so I can update content and fix errors.
Acceptance Criteria:
- Post edit form is accessible from the CMS.
- Editor can edit all post fields.
- Changes are visible on the public page after saving.

US-021
Title: Deleting a blog post
Description: As an editor, I want to delete a blog post so I can keep the news section current.
Acceptance Criteria:
- CMS enables deletion of a blog post.
- After deletion, the post disappears from the article list and is not publicly accessible.

### Security and Access
US-013
Title: Secure CMS access
Description: As an editor, I want to log in to the CMS using a password so I can protect data from unauthorized access.
Acceptance Criteria:
- CMS requires login (username + password).
- Passwords stored securely (hashing).
- No CMS access for unauthenticated users.

### Communication
US-014
Title: Contacting the team
Description: As a user, I want to send an email to the team so I can report feedback or bugs.
Acceptance Criteria:
- Page contains a contact form or email address.
- After sending a message, the user receives a confirmation of delivery.

## 6. Success Metrics
- Number of active application users.
- Number of object searches and filtrations.
- Average time spent on the object page.
- Number of printed object pages.
- Number and content of email submissions (positive feedback).
- Database consistency (100% of objects with title, description, and min. one photo).
- WCAG compliance (min. level AA).
- Beta version delivered on schedule (5 months).
- Number of homepage visits and navigations from it to the catalog, map, or blog.
- Number of article page views and average time spent on the blog.
- Number of navigations from the blog and homepage to object pages.

## 7. Change Classification

### Change 1: Homepage with project information
- Category: New Feature
- Size: Medium
- Priority: Must have for beta version as the main entry point to the product
- Description: Adding a public homepage with project description, main navigation, and latest objects section.

### Change 2: Blog with news/articles and latest objects section
- Category: New Feature
- Size: Medium / Large
- Priority: Should have
- Description: Adding a simple, CMS-managed blog module supporting the catalog and directing traffic to objects.

## 8. Impact Analysis

### Components and features affected by the change
- Public navigation and information architecture of the service.
- Homepage.
- Article listing page.
- Article detail page.
- CMS and content model for blog posts.
- Data source for the latest objects section.

### Impact on schedule and resources
- Changes extend the beta scope, but given the current state of the project, they remain low-risk.
- Main design impact concerns the homepage, article listing, and article template.
- Main development impact concerns routing, blog post model, CMS, and latest objects section integration.

### Technical dependencies and side effects
- It is necessary to add a new content type: Article / Post.
- The latest objects list must be based on the object's addition or publication date.
- The blog should use existing catalog data for the latest objects section.
- CMS scope should remain lightweight, without extensive publishing workflows.

### Impact on work already completed or started
- Based on the repository, it is assumed that the project is at the PRD stage.
- Changes do not require refactoring of existing code, but should be taken into account before starting information architecture and CMS implementation.

### Impact on user experience and product consistency
- The homepage strengthens understanding of the product purpose and organizes the user's first contact with the service.
- The blog increases the freshness and credibility of the project, but remains a supporting layer for the catalog, not a separate product.
- Product consistency requires maintaining the catalog, map, and object pages as the main value paths.

## 9. Change Deployment Strategy

### Deployment Recommendation
- The homepage should be deployed within the current beta scope.
- The blog and article posts should be deployed within the current beta scope as a simplified editorial feature.
- Advanced blog features should be deferred to a later product version.

### Refactoring Needs
- At the current stage, no refactoring needs exist.
- If implementation starts outside the repository before the requirements update, the greatest change risk will concern routing, main navigation, content model, and the CMS panel.

### Testing Strategy
- Homepage tests:
  - correct display of project description and main navigation links,
  - correct fetching and display of latest objects,
  - correct behavior on mobile and desktop devices.
- Blog tests:
  - correct order of posts on the list,
  - correct rendering of the article page,
  - correct linking to object pages from the latest objects section.
- CMS tests:
  - ability to add, edit, and delete a post,
  - validation of required fields for posts.
- Regression tests:
  - no negative impact on the catalog, filtering, map, object page, and print.

## 10. Updated Technical Specifications and Beta Scope
- New content type: Article / Post.
- New public views: homepage, article listing, article detail.
- Latest objects section uses existing object data source.
- Public access remains open, without end-user accounts.
- Beta version now includes not only the catalog and object CMS, but also a lightweight presentation and editorial layer.

## 11. Stakeholder Impact and Communication

### Stakeholders affected by the change
- Product owner / project initiator.
- Developer.
- Designer.
- Editorial team using the CMS.
- End users visiting the service.

### Recommended communication to the team
- The change should be communicated as a controlled scope extension that improves the first contact with the product and content freshness.
- It should be emphasized that the beta blog is intended to remain simple and supportive of the catalog.
- The priority remains the catalog, search engine, map, and object pages.

## 12. Risks and Mitigation Measures

### Risks of deploying changes during planning
- Expanding a simple blog into too extensive a publishing platform.
- CMS scope creep beyond the original simplicity assumption.
- Team attention being split between the catalog and additional public templates.
- Risk of beta delay if the presentation layer begins to dominate over core features.

### Mitigation Measures
- Maintain a strict v1 scope for the blog: post list, post detail, basic fields, and no social features.
- Use the existing object data model for the latest objects section.
- Treat the homepage and blog as an entry and discovery layer, not separate product goals.
- Confirm beta scope before starting screen design and the CMS model.

### Business Risks of Not Deploying Changes
- Weaker first impression of the user without a homepage explaining the project.
- Lower discoverability and less ability to build a narrative around the project.
- Fewer reasons for regular user returns without a news section.

## 13. Final Recommendation
- Include the homepage in the mandatory beta scope.
- Include blog / news in the beta scope as a simple CMS-managed module.
- Maintain a limited functional scope for the blog and do not expand it with social features or advanced publishing workflows.
- Maintain the objects catalog, map, and detail pages as the main product core.
