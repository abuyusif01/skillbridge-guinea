# SkillBridge Guinée — Interface sample

Static HTML/CSS prototype of the SkillBridge Guinea app (FYP1 concept) built to
explore the visual design before implementation.

- No frameworks, no build step, no external fonts or images.
- Bilingual French / English UI, light and dark themes.
- Mobile-first, low-bandwidth friendly.
- All data is fictional. Buttons that would hit a backend show a demo toast.

## Open it

Live demo:
<https://pub-90fe77748578448db8dc87ca9d9ac523.r2.dev/index.html>

From this folder:

```bash
python3 -m http.server 8000
```

Then open <http://localhost:8000>. You can also open `index.html` directly in a
browser.

## Pages

| File | Screen | Purpose |
|---|---|---|
| `index.html` | Home | Hero, search by trade + location, how it works, popular trades |
| `results.html` | Search | Filters + worker result cards |
| `profile.html` | Worker profile | Bio, skills, ratings, reviews, booking CTA |
| `booking.html` | Booking | Booking request form with summary |
| `booking-success.html` | Confirmation | Request sent confirmation |
| `dashboard.html` | Pro dashboard | Stats, pending / accepted / completed bookings |
| `review.html` | Leave a review | Star rating + comment form |
| `review-thanks.html` | Review confirmation | Review submitted confirmation |
| `admin.html` | Administration | Users, reviews and bookings moderation tabs |
| `login.html` | Log in | Login form |
| `register.html` | Sign up | Client / professional registration |

## Language & theme

Both controls sit in the header on every page.

- **FR / EN** switches the whole interface between French and English. French is
  the default. The choice is stored under `sb-lang` in `localStorage`.
- **Sun / moon** switches between light and dark themes. The first visit follows
  the operating system preference; an automatic OS change keeps syncing until
  the visitor picks a theme manually, then their choice is stored under
  `sb-theme`.
- Translations live in `js/i18n-data.js`; the runtime is `js/i18n.js`.
- Theme tokens are in `css/style.css` under `:root` and `:root[data-theme="dark"]`.
- The mobile menu, toast messages and accessibility labels follow the selected
  language too.
- Saved English avoids a French flash on navigation via a tiny pre-paint class.

## Demo behaviour

- Mobile menu and toast feedback: `js/app.js`.
- Tabs (dashboard/admin), role switcher (register) and star rating (review) work
  with CSS only.
- Forms with `data-demo-submit` redirect to a static page instead of posting.
- Review rating updates live; the chosen score is passed to the thank-you page.
- Language and theme preferences persist across pages and reloads.

## Accessibility & UX hardening

- Headings follow a valid outline; auth side panels no longer precede the `h1`.
- Decorative avatars are hidden from screen readers; star ratings expose a
  numeric label with `role="img"`.
- Repeated controls get unique accessible names, e.g.
  "Suspend — Mamadou Diallo", "View profile — Ibrahima Sow".
- Mobile admin tables collapse into labelled cards instead of horizontal scroll.
- Contrast tokens meet WCAG AA for body text and status badges in both themes.
- Native controls follow the theme via `color-scheme` (date pickers, checkboxes,
  scrollbars).
- Form fields are all labelled; focus rings, skip link and reduced-motion
  support remain in place.
- Professional counts are consistent across the home, admin and trade cards
  (437).

## Design notes

- **Mobile-first**: single-column layouts below ~640 px, bottom navigation bar,
  40 px+ tap targets for primary controls.
- **Colour**: blue `#1d4ed8` primary, green for available/success, amber for
  ratings and pending states. Dark mode redefines the tokens rather than
  duplicating component styles.
- **Typography**: system font stack only, so no network request is needed.
- **Assets**: avatars are generated initials; icons are inline SVG. This keeps
  pages small for slow 3G / 512 kbps connections.
- **Accessibility**: skip link, labelled fields, visible focus rings, ARIA labels
  on icon-only controls, translated accessibility labels, reduced-motion support.
- **No payments**: consistent with the FYP scope — customers pay the worker
  directly after the job.

## Not included

- Real authentication, database, search or booking logic.
- Server-side localisation. The prototype swaps text in the browser; the PHP
  implementation should store translations server-side, use stable message IDs
  and set `<html lang>`.
- Shared page templates. The header/footer shell is repeated in each HTML file;
  the PHP implementation should use includes.
- Automated browser tests.
- Photos of workers — placeholders are initials until real profile photos are
  added.

The PHP/MySQL implementation can reuse this layout and the CSS tokens in
`css/style.css`.
