# RFCs — Sightseeing Objects Catalog of Poland

> **Terminology:** "sightseeing object" = *obiekt krajoznawczy*; "voivodeship" = *województwo*; "news" = *aktualności*

## Overview

This document defines the implementation roadmap for the Kanon project — a public web application presenting a catalog of Polish sightseeing objects with map, filters, blog, and a CMS for the editorial team.

The project is broken into **7 RFCs** implemented **strictly sequentially**. Each RFC is fully implementable after all preceding RFCs are completed. No parallel implementation is permitted.

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| CMS/Admin | Filament v4 |
| Public static pages | Blade templates |
| Interactive catalog | Inertia v3 + Svelte 5 |
| Database | PostgreSQL + PostGIS |
| Search | Laravel Scout (database driver) |
| Map | Leaflet |
| Media | Spatie Laravel Media Library |
| Styling | Tailwind CSS v4 |
| Testing | Pest v4 |

## Implementation Roadmap

```
RFC-001  Database Foundation & Core Models
   │
   ▼
RFC-002  Media Management Layer
   │
   ▼
RFC-003  Filament CMS
   │
   ▼
RFC-004  Public Pages (Homepage, Blog, Contact)
   │
   ▼
RFC-005  Interactive Catalog (Inertia + Svelte)
   │
   ▼
RFC-006  Object Detail Page
   │
   ▼
RFC-007  Advanced Features (Nearby, Print, Polish)
```

## RFC Summary Table

| ID | Title | Complexity | Pages Addressed | Dependencies |
|---|---|---|---|---|
| [RFC-001](RFC-001-Database-Foundation.md) | Database Foundation & Core Models | Medium | — | None |
| [RFC-002](RFC-002-Media-Management.md) | Media Management Layer | Medium | — | RFC-001 |
| [RFC-003](RFC-003-Filament-CMS.md) | Filament CMS | High | /cms/* | RFC-001, RFC-002 |
| [RFC-004](RFC-004-Public-Pages.md) | Public Pages (Homepage, Blog, Contact) | Medium | /, /aktualnosci, /aktualnosci/[slug], /kontakt | RFC-001 |
| [RFC-005](RFC-005-Interactive-Catalog.md) | Interactive Catalog (Inertia + Svelte) | High | /katalog | RFC-001, RFC-002 |
| [RFC-006](RFC-006-Object-Detail.md) | Object Detail Page | Medium | /obiekty/[slug] | RFC-001, RFC-002, RFC-005 |
| [RFC-007](RFC-007-Advanced-Features.md) | Advanced Features (Nearby, Print, Polish) | Medium | Enhancements | RFC-001, RFC-005, RFC-006 |

## Dependency Matrix

| RFC | Predecessors | Successors |
|---|---|---|
| RFC-001 | — | RFC-002, RFC-004, RFC-005, RFC-006, RFC-007 |
| RFC-002 | RFC-001 | RFC-003, RFC-005, RFC-006 |
| RFC-003 | RFC-001, RFC-002 | — (enables content creation for all public pages) |
| RFC-004 | RFC-001 | RFC-007 (contact form enhancement) |
| RFC-005 | RFC-001, RFC-002 | RFC-006, RFC-007 |
| RFC-006 | RFC-001, RFC-002, RFC-005 | RFC-007 |
| RFC-007 | RFC-001, RFC-005, RFC-006 | — |

## Critical Path

The critical path (longest dependency chain) is:

```
RFC-001 → RFC-002 → RFC-005 → RFC-006 → RFC-007
```

This means the interactive catalog and object detail features form the core development spine. RFC-003 (CMS) and RFC-004 (public pages) can branch off after RFC-002/RFC-001 respectively but don't block the critical path.

## User Stories Mapping

| User Story | RFC(s) |
|---|---|
| US-001: Browsing catalog | RFC-005 |
| US-002: Filter by voivodeship | RFC-005 |
| US-003: Filter by category | RFC-005 |
| US-004: Filter by UNESCO | RFC-005 |
| US-005: Search by name | RFC-005 |
| US-006: Nearby objects | RFC-007 |
| US-007: Map view | RFC-005 |
| US-008: Object detail page | RFC-006 |
| US-009: Print object page | RFC-007 |
| US-010: Add object (CMS) | RFC-003 |
| US-011: Edit object (CMS) | RFC-003 |
| US-012: Delete object (CMS) | RFC-003 |
| US-013: Secure CMS access | RFC-003 |
| US-014: Contact team | RFC-004 |
| US-015: Homepage | RFC-004 |
| US-016: Latest objects | RFC-004, RFC-005 |
| US-017: Browse articles | RFC-004 |
| US-018: Read article | RFC-004 |
| US-019: Add article (CMS) | RFC-003 |
| US-020: Edit article (CMS) | RFC-003 |
| US-021: Delete article (CMS) | RFC-003 |
