# SkillBridge Guinea

FYP2 platform connecting skilled tradespeople with customers in Guinea.
Laravel 12 backend with a bilingual French / English Blade frontend.

The original static design prototype is preserved in [`design/`](design/) —
its UI, copy and component styles are the reference for the Blade views.

## Stack

- **Laravel 12** on PHP 8.4 (inside Sail)
- **MySQL 8.4**
- **Laravel Sail** for Docker-based local development
- **Vite** for frontend assets
- MySQL-backed sessions, cache and queues (default Laravel setup)
- GitHub Actions for tests and code style

## Repository layout

```
app/                 Laravel application code
bootstrap/           framework bootstrap
config/              configuration
database/            migrations, seeders, factories
design/              static HTML/CSS/JS design reference
docs/                setup and backend plan
public/              web root
resources/           Blade views, CSS, JS
routes/              web / console routes
tests/               PHPUnit / Pest tests
compose.yaml         Sail services (PHP 8.4 + MySQL 8.4)
```

## Local setup — Linux, macOS or WSL2

Prerequisites: Docker (or Docker Desktop), Git, PHP 8.2+ and Composer on the
host only to run the Sail shortcut.

```bash
git clone https://github.com/abuyusif01/skillbridge-guinea.git
cd skillbridge-guinea

cp .env.example .env
composer install

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Open <http://localhost:8000>.

> Rootless Docker on Linux: if storage or npm writes fail, run
> `chmod -R a+rwX storage bootstrap/cache`, then use
> `./vendor/bin/sail exec laravel.test npm install` once.

`migrate --seed` imports six trades and demo accounts. All demo passwords are
`password`:

| Role | Email |
|---|---|
| Admin | `admin@skillbridge.test` |
| Customer | `amadou@example.test` |
| Professional | `mamadou@example.test` |

Demo users are skipped when `APP_ENV=production`.

## What works now

- Public landing page with DB-backed trades, stats and a featured professional
- Search by trade, city and minimum rating; public worker profiles with reviews
- Customer booking form, confirmation and dashboard
- Worker dashboard: accept, decline and complete requests
- Customer review form, one review per completed booking
- Admin dashboard: suspend/reactivate users, delete reviews, inspect bookings
- Registration for customers and professionals; login by e-mail or phone
- Password reset by e-mail link (written to the local log, see below)
- In-app notification centre with unread badge: new booking requests, accept,
  decline, completion and new reviews; mark one or all as read
- Suspended accounts blocked at login and on active sessions
- Role middleware (`role:admin`) and policies for booking/review actions
- FR/EN and light/dark controls on every Blade page

Production deployment, SMS delivery and queued mail are next.

### Notifications and password reset in local development

Booking events create a database notification (shown in the header bell and
`/notifications`) and send an e-mail. Password-reset links are also sent by
e-mail. The default `.env` uses `MAIL_MAILER=log`, so every message is written
to `storage/logs/laravel.log` instead of leaving the machine — open that file
to copy a reset link during a demo.

Notifications are sent synchronously so the demo works without a queue
worker. Before production, move the notification classes to `ShouldQueue` and
run `php artisan queue:work`.

Run tests inside Sail:

```bash
./vendor/bin/sail test
```

## Windows

The end user runs the app locally on Windows. Two supported paths:

1. **WSL2 + Docker Desktop + Sail** — recommended, matches Linux and production.
2. **Laravel Herd** — native Windows PHP, quicker start, MySQL via Docker/Herd Pro
   or SQLite for a quick preview.

Full step-by-step: [`docs/windows-setup.md`](docs/windows-setup.md).

## Design reference

- Static prototype: [`design/index.html`](design/index.html)
- Live prototype: <https://pub-90fe77748578448db8dc87ca9d9ac523.r2.dev/index.html>
- FR/EN toggle and light/dark theme are in the prototype; production
  localisation will move to Laravel `lang/` files.

## Backend plan

See [`docs/backend-plan.md`](docs/backend-plan.md) for the domain model,
authentication plan, milestones and open decisions.

## Deployment

Target is a Linux host with MySQL 8 and HTTPS. Windows users only need a
browser. Deployment platform decision is still open (Fly.io / Railway / Forge /
VPS).
