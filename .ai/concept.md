**MVP Concept Description**

**1. Core MVP Hypothesis/Goal**

Tourists planning or exploring a trip in Poland will find value in a centralized catalog of krajoznawcze objects if they can browse those objects through a clear **map + list** experience and open simple object detail pages.

Primary learning objective: validate whether map/list browsing creates enough interest for tourists to click into object detail pages.

Grounded in: PRD section 2, **US-001**, **US-002**, **US-007**, **US-008**; UX “Catalog View” and “Object Detail View”.

**2. Target Audience**

Early adopter segment: **tourists planning or considering a trip in Poland**.

They want a quick way to discover interesting places geographically, especially by region/województwo, without searching across many inconsistent sources.

**3. Problem Solved**

The MVP addresses the narrow problem that tourist information about krajoznawcze objects is scattered and hard to browse geographically.

MVP focus: help tourists visually browse objects on a map, scan a supporting list, narrow by województwo, and inspect basic object details.

**4. Minimum Feature Set**

**IN**

- `/katalog` map-first browsing view.
- Marker for every published object.
- Object list/card grid shown alongside or below the map.
- Województwo filter.
- Marker/card click opens simple object detail page.
- Simple object detail page with:
  - title
  - main photo
  - gallery
  - short description
  - województwo
  - category
  - back link to catalog/map

**OUT**

- Fuzzy name search.
- Category filter.
- UNESCO filter.
- Nearby objects.
- Practical info: opening hours, ticket prices, website.
- Print view.
- Blog/aktualności.
- Full public homepage beyond a minimal entry/link if needed.
- Full CMS editing workflows, except any lightweight internal mechanism needed to seed object data.

**5. Key Constraints**

- MVP delivery window: **8 weeks**.
- Keep scope focused on validating browsing behavior, not full beta completeness.
- Use PRD beta constraints as background: small team, 1 developer + 1 designer.
- Polish language only.
- Public users do not need accounts or login.

**6. Initial Success Metrics Idea**

Primary metric: percentage of tourist users who click from the **map or object list** into an object detail page.

Supporting signals:

- number of marker clicks
- number of list/card clicks
- usage of województwo filter
- time spent on object detail/gallery
- qualitative feedback on whether browsing helped users discover places worth visiting