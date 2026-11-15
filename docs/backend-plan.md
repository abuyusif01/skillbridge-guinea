# Backend plan

## Decisions so far

- Laravel 12 on PHP 8.4, MySQL 8.4, Blade views (no separate SPA).
- Repository root is the Laravel app; `design/` keeps the static prototype.
- Local development through Laravel Sail so Windows and Linux match.
- Server-rendered pages; JavaScript only for progressive enhancement.
- Localisation server-side (`lang/fr`, `lang/en`, locale middleware), not the
  prototype's browser dictionary.
- Windows user runs locally; production will be a Linux host with HTTPS.

## Domain model

### users

| column | notes |
|---|---|
| id | |
| name | required |
| email | unique, nullable if phone-only signup is supported |
| phone | unique, required, used for contact/login |
| password | bcrypt |
| role | `customer`, `worker`, `admin` |
| city | prefecture / commune |
| locale | `fr` / `en` |
| avatar_path | nullable |
| is_active | admin suspension |
| email_verified_at | nullable |
| timestamps | |

### trades

| column | notes |
|---|---|
| id | |
| slug | unique, e.g. `electrician` |
| name_fr / name_en | localised labels |
| icon | UI icon key |
| is_active | |

### worker_profiles

| column | notes |
|---|---|
| id | |
| user_id | unique, FK users |
| trade_id | FK trades |
| bio | text |
| experience_years | unsigned tinyint |
| service_area | text |
| rate_min | nullable integer, GNF |
| is_available | boolean |
| verified_at | nullable timestamp |
| timestamps | |

### bookings

| column | notes |
|---|---|
| id | |
| customer_id | FK users |
| worker_id | FK users |
| trade_id | FK trades |
| description | text |
| preferred_date | date |
| time_slot | string / enum |
| address | string |
| contact_phone | string |
| status | `pending`, `accepted`, `declined`, `completed`, `cancelled` |
| responded_at | nullable |
| completed_at | nullable |
| timestamps | |

### reviews

| column | notes |
|---|---|
| id | |
| booking_id | unique, FK bookings |
| customer_id | FK users |
| worker_id | FK users |
| rating | 1–5 |
| comment | nullable text |
| status | `published`, `reported`, `hidden` |
| timestamps | |

## Business rules

- Customers can book only workers with an active worker profile.
- Workers accept or decline pending requests; only accepted bookings can be
  marked completed by the worker.
- A review can be created only by the customer, only after completion, and only
  once per booking.
- Ratings are aggregated on the worker profile (average + count).
- Admin can suspend users, hide reported reviews, and view bookings.
- No online payments. Payment happens directly between customer and worker.

## Security baseline

- Laravel's bcrypt password hashing, CSRF protection and signed sessions.
- Form Requests for all writes; policies/gates for booking, review and admin
  actions.
- Authorisation checks on every booking/review state transition.
- Rate-limit login and review submission.
- Validate and store uploads on non-public disk; serve through controllers if
  avatars are added.
- Admin routes under `auth` + `role:admin` middleware.
- No secrets in the repo; `.env` stays ignored.

## Milestones

1. **Scaffold** — Laravel, Sail, MySQL, Windows guide, CI. *(done in this branch)*
2. **Auth + roles** — Breeze Blade auth, user role/phone/city fields, policies.
3. **Schema** — trades, worker profiles, bookings, reviews; factories + seeders
   with the six trades and demo workers from the design.
4. **Blade frontend** — layout/header/footer from `design/`, FR/EN translations,
   theme toggle server-side.
5. **Customer flow** — search, filters, worker profile, booking request.
6. **Worker dashboard** — pending/accepted/completed, accept/decline/complete.
7. **Reviews + admin** — review submission, moderation, user management.
8. **Tests + deploy** — feature tests, CI green, Linux deploy, R2 for media if
   needed.

## Open decisions

- Auth starter: Laravel Breeze (recommended, Blade) vs Fortify + custom views.
- Phone-only registration / login by phone or email.
- Exact booking time-slot representation.
- Deployment platform: Fly.io, Railway, Forge or VPS.
- Email/SMS notifications provider for booking status changes.
- Avatar storage: local disk first, Cloudflare R2 with S3 driver in production.
