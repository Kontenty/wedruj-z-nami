# RFCs — Sightseeing Objects Catalog of Poland

> **Terminology:** "sightseeing object" = _obiekt krajoznawczy_; "voivodeship" = _województwo_; "news" = _aktualności_

## Overview

This document defines the implementation roadmap for the Kanon project — a public web application presenting a catalog of Polish sightseeing objects with map, filters, news, and a CMS for the editorial team.

The project is broken into **7 RFCs** implemented in dependency order. Each RFC is fully implementable after its listed predecessors are completed; RFCs may branch where the dependency graph permits.

## Technology Stack

| Layer               | Technology                      |
| ------------------- | ------------------------------- |
| Backend             | Laravel 13, PHP 8.4             |
| Auth                | Laravel Fortify                 |
| CMS/Admin           | Filament v4                     |
| Public static pages | Blade templates                 |
| Interactive catalog | Inertia v3 + Svelte 5           |
| Database            | MariaDB 10.11 with spatial features |
| Search              | MariaDB `LIKE` on case-insensitive collation for beta; Scout later if needed |
| Map                 | MapLibre                        |
| Media               | Spatie Laravel Media Library, with PRD media attribution metadata (`author`, `source`) and public storage symlink serving |
| Styling             | Tailwind CSS v4                 |
| Testing             | Pest v4                         |

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
RFC-004  Public Pages (Homepage, News)
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

| ID                                        | Title                                     | Status         | Complexity | Pages Addressed                      | Dependencies              |
| ----------------------------------------- | ----------------------------------------- | -------------- | ---------- | ------------------------------------ | ------------------------- |
| [RFC-001](RFC-001-Database-Foundation.md) | Database Foundation & Core Models         | ✅ Implemented | Medium     | —                                    | None                      |
| [RFC-002](RFC-002-Media-Management.md)    | Media Management Layer                    | ✅ Implemented | Medium     | —                                    | RFC-001                   |
| [RFC-003](RFC-003-Filament-CMS.md)        | Filament CMS                              | Pending        | High       | /cms/\*                              | RFC-001, RFC-002          |
| [RFC-004](RFC-004-Public-Pages.md)        | Public Pages (Homepage, News)             | Pending        | Medium     | /, /aktualnosci, /aktualnosci/[slug] | RFC-001, RFC-002          |
| [RFC-005](RFC-005-Interactive-Catalog.md) | Interactive Catalog (Inertia + Svelte)    | Pending        | High       | /katalog                             | RFC-001, RFC-002          |
| [RFC-006](RFC-006-Object-Detail.md)       | Object Detail Page                        | Pending        | Medium     | /katalog/[slug]                      | RFC-001, RFC-002, RFC-005 |
| [RFC-007](RFC-007-Advanced-Features.md)   | Advanced Features (Nearby, Print, Polish) | Pending        | Medium     | Enhancements                         | RFC-001, RFC-005, RFC-006 |

## Dependency Matrix

| RFC     | Predecessors              | Successors                                        |
| ------- | ------------------------- | ------------------------------------------------- |
| RFC-001 | —                         | RFC-002, RFC-004, RFC-005, RFC-006, RFC-007       |
| RFC-002 | RFC-001                   | RFC-003, RFC-004, RFC-005, RFC-006                |
| RFC-003 | RFC-001, RFC-002          | — (enables content creation for all public pages) |
| RFC-004 | RFC-001, RFC-002          | RFC-007                                           |
| RFC-005 | RFC-001, RFC-002          | RFC-006, RFC-007                                  |
| RFC-006 | RFC-001, RFC-002, RFC-005 | RFC-007                                           |
| RFC-007 | RFC-001, RFC-005, RFC-006 | —                                                 |

## Critical Path

The critical path (longest dependency chain) is:

```
RFC-001 → RFC-002 → RFC-005 → RFC-006 → RFC-007
```

This means the interactive catalog and object detail features form the core development spine. RFC-003 (CMS) and RFC-004 (public pages) can branch off after RFC-002/RFC-001 respectively but don't block the critical path.

## User Stories Mapping

| User Story                               | RFC(s)           |
| ---------------------------------------- | ---------------- |
| US-001: Browse objects catalog           | RFC-005          |
| US-002: Filter by voivodeship            | RFC-005          |
| US-003: Filter by object type            | RFC-005          |
| US-003A: Filter by UNESCO designation    | RFC-005          |
| US-004: Search by name/text              | RFC-005          |
| US-005: Explore map                      | RFC-005          |
| US-006: View object details              | RFC-006, RFC-007 |
| US-007: Read news                        | RFC-004          |
| US-008: Add object                       | RFC-003          |
| US-009: Edit object                      | RFC-003          |
| US-010: Manage news entry                | RFC-003          |
| US-011: Secure CMS access                | RFC-003          |
