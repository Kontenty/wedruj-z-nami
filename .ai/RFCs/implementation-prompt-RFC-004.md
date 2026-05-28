# Implementation Prompt: RFC-004

**Title:** Public Pages (Homepage, Blog, Contact)  
**ID:** RFC-004  
**Brief Description:** Build all Blade-based public pages: homepage with project description and latest objects, articles listing and detail pages, contact page with form and email delivery. Includes shared layout, navigation, responsive design, and Tailwind Typography.

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*; "article" = *artykuł*

---

You are implementing RFC-004 for the Kanon project. This RFC creates the public-facing informational and editorial layer.

## Prerequisites

- RFC-001 must be completed (models and seed data exist)

## What to Build

1. **Shared public layout** (`layouts/public.blade.php`) with header, footer, responsive navigation
2. **Homepage** (`HomeController` + `home.blade.php`) with hero, "for whom" section, latest objects grid, latest articles teaser, contact prompt
3. **Article listing** (`ArticleController::index` + `articles/index.blade.php`) with paginated articles grid and latest objects section
4. **Article detail** (`ArticleController::show` + `articles/show.blade.php`) with Markdown rendering, back link, contextual CTA
5. **Contact page** (`ContactController` + `contact.blade.php`) with form, validation, email sending
6. **Contact email** (`ContactMessage` mailable + email template)
7. **Navigation components** (public header with mobile hamburger, footer)
8. **Install Tailwind Typography** for Markdown prose styling
9. **Write Pest tests** for all controllers and views

## Key Files to Create/Modify

- `routes/web.php` — add public routes
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/ArticleController.php`
- `app/Http/Controllers/ContactController.php`
- `app/Mail/ContactMessage.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/components/public-header.blade.php`
- `resources/views/components/public-footer.blade.php`
- `resources/views/home.blade.php`
- `resources/views/articles/index.blade.php`
- `resources/views/articles/show.blade.php`
- `resources/views/contact.blade.php`
- `resources/views/emails/contact.blade.php`
- `tests/Feature/HomePageTest.php`
- `tests/Feature/ArticlePageTest.php`
- `tests/Feature/ContactPageTest.php`

## Critical Requirements

- Homepage shows up to 4 latest published objects and 3 latest articles
- Article listing paginates (12 per page), reverse chronological
- Article body rendered from Markdown using `Str::markdown()`
- Contact form validates name, email, message (all required)
- Contact form sends email via Mailable on success
- Unpublished objects/articles never appear on public pages
- Responsive: mobile hamburger nav, card grids adapt to screen size
- Tailwind Typography used for prose styling on article pages

## Do NOT

- Do not install Filament or create CMS pages
- Do not create the interactive catalog (that's RFC-005)
- Do not create the object detail page (that's RFC-006)
- Do not modify existing Fortify auth routes or views

## Reference

Read the full RFC at: `.ai/RFCs/RFC-004-Public-Pages.md`
