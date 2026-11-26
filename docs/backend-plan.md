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

### notifications

Laravel's standard polymorphic notifications table (`uuid` id, `type`,
`notifiable_type`, `notifiable_id`, `data` JSON, `read_at`). Database
notifications power the in-app inbox; the same events send an e-mail.

## Business rules

- Customers can book only workers with an active worker profile.
- Workers accept or decline pending requests; only accepted bookings can be
  marked completed by the worker.
- Customers can cancel pending or accepted bookings; completion, decline and
  cancellation are final states.
- Workers can edit their trade, biography, experience, service area, starting
  rate and availability. Unavailable workers are hidden from search.
- A review can be created only by the customer, only after completion, and only
  once per booking.
- Ratings are aggregated on the worker profile (average + count).
- Admin can suspend users, hide reported reviews, and view bookings.
- No online payments. Payment happens directly between customer and worker.
- Booking activity notifies the other party: a new request reaches the worker;
  accept, decline, completion and customer cancellation reach the relevant
  party; a new review reaches the worker. Each notification is stored in the
  inbox and e-mailed.
- Password reset uses Laravel's broker with a signed, expiring token. The
  response never reveals whether an e-mail address belongs to an account.

## Security baseline

- Laravel's bcrypt password hashing, CSRF protection and signed sessions.
- Form Requests for all writes; policies/gates for booking, review and admin
  actions.
- Authorisation checks on every booking/review state transition.
- Rate-limit login and review submission.
- Rate-limit password-reset requests; always return the same response for known
  and unknown e-mail addresses.
- Validate and store uploads on non-public disk; serve through controllers if
  avatars are added.
- Admin routes under `auth` + `role:admin` middleware.
- No secrets in the repo; `.env` stays ignored.

## Milestones

1. **Scaffold** — Laravel, Sail, MySQL, Windows guide, CI. *(done)*
2. **Auth + roles** — custom session auth: e-mail/phone login, customer/worker
   registration, role middleware, auth tests. *(done in `feat/auth-roles`)*
3. **Schema** — trades, worker profiles, bookings, reviews; factories + seeders
   with the six trades and demo workers from the design. *(done in scaffold)*
4. **Blade frontend** — layout/header/footer from `design/`, FR/EN translations,
   theme toggle server-side. Landing page and auth screens are live; search,
   profile and booking pages remain. Assets currently mirror
   `design/css|js` under `public/assets`; migrate them to Vite.
5. **Customer flow** — search, filters, worker profile, booking request. *(done)*
6. **Worker dashboard** — pending/accepted/completed, accept/decline/complete. *(done)*
7. **Reviews + admin** — review submission, moderation, user management. *(done)*
8. **Recovery, notifications + pagination** — password reset, notification inbox
   and booking/review e-mails, paginated search with a portable rating filter,
   24 new feature tests. *(done)*
9. **Profile + cancellation** — worker profile editing and availability,
   customer cancellation with worker notification, 14 new feature tests. *(done)*
10. **Tests + local delivery** — 98 feature tests and CI green; admin worker
    verification, server-side locale switching, SMS notifications and queued
    mail remain. Production deployment is out of scope for the local FYP demo.

## Open decisions

- Auth starter: Laravel Breeze (recommended, Blade) vs Fortify + custom views.
- Phone-only registration / login by phone or email.
- Exact booking time-slot representation.
- Deployment platform: Fly.io, Railway, Forge or VPS.
- Email/SMS notifications provider for booking status changes. E-mail is wired
  to Laravel's mailer; an SMS channel is still open.
- Avatar storage: local disk first, Cloudflare R2 with S3 driver in production.
