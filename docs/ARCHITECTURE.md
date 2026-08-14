# Project Architecture

## Overview
Production-ready GSM service ordering platform built on Laravel 12 (compatible with PHP 8.2+). Customers browse legitimate GSM/device services, submit orders, upload payment proof, and track status. Admins manage services, dynamic forms, pricing, payments, orders, customers, content, and staff.

## Layered Architecture
```
routes/web.php ──► Controllers ──► Services ──► Repositories ──► Models ──► MySQL
                     │               │             │
                     │            Business        Data access
                     │            logic           (Eloquent)
                     ▼
          Form Requests (validation)
                     │
                     ▼
          Policies (authorization) / Middleware
                     │
                     ▼
          Notifications / Jobs / Events (queued side-effects)
```

Rules:
- Controllers: thin. Parse request → validate → delegate to service → respond.
- Services: business logic. Never hold request state. No SQL.
- Repositories: Eloquent data access. Used where it simplifies reuse.
- Models: Eloquent relations, casts, scopes only.
- Form Requests: server-side validation, never trusted frontend.
- Policies/Middleware: server-side authorization on every admin action.
- Notifications/Jobs: Telegram, email, internal notifications always queued; must never block or roll back the DB transaction.

## Technology
- Backend: PHP 8.2+ (dev: 8.2.12), Laravel 12, Eloquent, policies, middleware, queues, notifications
- Database: MariaDB 10.4 (dev; production target MySQL 8+). Times stored in UTC.
- Frontend: Blade + Tailwind CSS + vanilla JS / Alpine.js, mobile-first
- Storage: Laravel private filesystem (storage/app/private) for proofs/results
- Notifications: Telegram Bot API, SMTP mail
- Server (production): Ubuntu, Nginx, PHP-FPM, Supervisor (queue), Cron (scheduler)

## Data Integrity
- Orders store snapshots: `service_name_snapshot`, `price_snapshot`, `currency_snapshot`.
- `order_field_values` preserve dynamic field values even if service fields change later.
- Soft deletes used for categories/services so historical references survive.
- Critical writes (orders, payments, status changes, refunds) run in DB transactions.

## Security Baseline
- CSRF on all web forms, SQL injection prevented via Eloquent/bindings, XSS via escaping/Blade.
- Rate limiting on login, register, password reset, order creation, order lookup, support, uploads.
- Private files served via authenticated, authorized, signed routes only.
- Secrets only in `.env`; `.env` never committed; audit logging of admin actions.

## Directory Layout
- app/Enums — status/type enums
- app/Helpers — global helpers
- app/Models — Eloquent models
- app/Repositories — data access layer
- app/Services — business logic
- app/Http/Controllers — web controllers (Admin + Customer)
- app/Http/Requests — form requests
- app/Policies — authorization
- app/Notifications — mail/telegram/database notifications
- app/Jobs — queued jobs
- app/Events, app/Listeners — domain events
- docs/ — architecture, DB diagram, route map, permission matrix, staged plan, deployment
