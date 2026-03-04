# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development

```bash
# Start services (PostgreSQL + Mailpit)
docker compose up -d

# Clear cache
php bin/console cache:clear

# Run Tailwind CSS watcher / one-off build
php bin/console tailwind:build --watch
php bin/console tailwind:build

# Compile assets to public/ (production)
php bin/console asset-map:compile
```

### Database

```bash
php bin/console doctrine:migrations:migrate          # Run pending migrations
php bin/console doctrine:migrations:diff             # Generate migration from entity changes
php bin/console doctrine:fixtures:load               # Load sample data (Plans, Tools)
```

### Tests

```bash
./vendor/bin/phpunit                                 # All tests
./vendor/bin/phpunit tests/PdfGeneratorServiceTest.php  # Single file
./vendor/bin/phpunit --testdox                       # Verbose output
```

## Architecture

### Tech stack

Symfony 7.4 · PHP 8.2 · PostgreSQL · Tailwind CSS v4 (via `symfonycasts/tailwind-bundle`) · Symfony Asset Mapper (no Webpack/Vite) · React 18 + Stimulus (via importmap) · Gotenberg (headless Chromium, PDF API).

### Subscription model

Three layers interact to control feature access:

- **Plan** entity holds a Symfony role string (e.g. `ROLE_BASIC`) and `usageLimit`.
- **Tool** entity has a ManyToMany to Plan — each tool lists which plans can use it.
- **User** has a ManyToOne to Plan; `getRoles()` returns the plan's role, wiring into Symfony's security system.

Access check in `PdfController`: iterates `tool.plan` collection and calls `is_granted(plan.role)`.

### PDF generation flow

`PdfGeneratorService` (autowired, `$gotenbergUrl` from `.env`) wraps the Gotenberg HTTP API:
- URL tools → `POST /forms/chromium/convert/url`
- File/HTML tools → writes a temp file, `POST /forms/chromium/convert/html`

The response binary is streamed back inline as `application/pdf`.

### Frontend asset pipeline

Assets live in `assets/`. The entrypoint is `assets/app.js`. Twig loads everything via `{{ importmap('app') }}` in `base.html.twig`. Module mappings are declared in `importmap.php` — run `php bin/console importmap:install` after adding entries. CSS is processed by Tailwind; `@theme` in `assets/styles/app.css` defines custom tokens (colors, fonts).

### Email & async

Mailer uses SMTP (`MAILER_DSN`). In dev, Mailpit runs on port 8025 (web UI) / 1025 (SMTP). The Messenger transport is Doctrine-backed (`MESSENGER_TRANSPORT_DSN`); emails are dispatched synchronously (`sync` transport in `config/packages/messenger.yaml`).

### Key environment variables

| Variable | Purpose |
|---|---|
| `DATABASE_URL` | Doctrine DSN (PostgreSQL in Docker) |
| `GOTENBERG_URL` | Gotenberg service URL (`http://gotenberg:3000`) |
| `MAILER_DSN` | SMTP connection string |
| `MESSENGER_TRANSPORT_DSN` | Async queue backend |

### Templates & design system

All templates extend `base.html.twig`. Dark theme (`#08080c` background). CSS custom properties are defined via Tailwind `@theme`: `--font-heading` (Bricolage Grotesque), `--font-body` (DM Sans), named colors (`surface`, `border`, `muted`, `accent`, `highlight`). Animations (`animate-fade-up`, `animate-float`) and delays are in `app.css`.
