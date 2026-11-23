# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

F1Tipp is a Formula 1 prediction/betting game application. It's a Symfony refactoring of a legacy custom framework project, hosted at www.f1tipp.nhely.hu.

## Tech Stack

**Backend:** Symfony 6.4 LTS, Doctrine ORM 2.20 (PHP attribute mapping), EasyAdmin 4, Google OAuth2
**Frontend:** Vue.js 3 (plain, SFC-only, no runtime template compiler), Vue Router 4, Vue I18n, Webpack Encore
**Database:** MySQL 5.6 (Docker)
**Infrastructure:** Docker Compose (Nginx, PHP-FPM 8.4, MySQL, PHPMyAdmin)

## Commands

### Setup
```bash
docker-compose up -d
composer install
npm install
php bin/console doctrine:database:create
```

### Development
```bash
npm run watch          # Watch assets and rebuild
npm run dev            # Development build
npm run build          # Production build
```

### Testing
```bash
php bin/phpunit                    # Run all tests
php bin/phpunit tests/Path/To/Test # Run specific test
```

### Database
No Doctrine migrations (the bundle was removed); schema changes are applied by hand. To compare entities with the DB:
```bash
php bin/console doctrine:schema:update --dump-sql  # Show SQL needed to sync the schema (does not execute)
```

### Symfony Console
```bash
php bin/console cache:clear        # Clear cache
php bin/console debug:routes       # List routes
php bin/console debug:container    # List services
```

## Architecture

### Directory Structure
- `src/Controller/Admin/` - EasyAdmin CRUD controllers
- `src/Controller/Api/` - REST API endpoints
- `src/Controller/Cron/` - Scheduled task handlers
- `src/Controller/Page/` - Page controllers
- `src/Entity/` - Doctrine entities
- `src/LegacyService/` - Migrated legacy code from original framework
- `src/Rule/` - Betting rule system with pluggable types and attributes
- `assets/js/components/` - Vue single-file components
- `assets/js/router/` - Vue Router configuration
- `assets/js/stores/` - State management

### Key Entry Points
- `assets/js/main.js` - Vue 3 SPA setup
- `assets/js/app.js` - jQuery entry point
- `src/Controller/Page/ActualController.php` - home route rendering the Vue SPA shell (`templates/controller/page/actual.html.twig`)
- `public/index.php` - Symfony front controller

### Webpack Entry Points
Four entry points defined in `webpack.config.js`: app.js, module_reload.js, messages.js, main.js

## Local URLs

- **App:** http://127.0.0.1:8000/ (nginx container maps port 80 → host 8000)
- **PHPMyAdmin:** http://localhost:8080/ (root/root)

## Deploy (production package)

Production is hosted on Nethely and updated by the user via FTP sync (only changed files). Claude builds the package into `deploy/` (excluded via `.git/info/exclude`) from the latest committed `master`; the dev `vendor/` and `public/build` stay untouched. Don't use `.github/workflows/main.yml` (outdated: PHP 7.4, Node 14). From the repo root:

```bash
rm -rf deploy && mkdir deploy
git archive HEAD | tar -x -C deploy        # committed state only

docker exec -e COMPOSER_ALLOW_SUPERUSER=1 -w /var/www/f1tipp_sf4/deploy f1tipp-php-1 \
  composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
docker exec -e APP_ENV=prod -e APP_DEBUG=0 -w /var/www/f1tipp_sf4/deploy f1tipp-php-1 \
  php bin/console assets:install public --no-interaction      # public/bundles (EasyAdmin)
(cd deploy && ln -s ../node_modules node_modules && npx encore production)
```

Smoke test before cleanup (local DB, `pkill` is missing in the container, stop it via `/proc/*/cmdline`):
```bash
docker exec -d -e APP_ENV=prod -e APP_DEBUG=0 -e DATABASE_URL=mysql://root:root@mysql_db:3306/f1tippelek \
  -w /var/www/f1tipp_sf4/deploy f1tipp-php-1 php -S 127.0.0.1:8099 -t public public/index.php
docker exec f1tipp-php-1 curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8099/
```

Cleanup, so only production files remain (~8 640 files, ~123 MB; `vendor/` ~8 000 of it):
```bash
docker exec -w /var/www/f1tipp_sf4/deploy f1tipp-php-1 sh -c 'rm -rf var && mkdir var && chown -R 1000:1000 .'
cd deploy && rm -rf .docker .env.e2e .env.test .github .npmrc .gitignore README.md CLAUDE.md assets docker-compose.yml \
  docs node_modules package-lock.json package.json phpunit.xml.dist tests webpack.config.js bin/phpunit
```

Result: `.env`, `bin/console`, `composer.json/.lock`, `symfony.lock`, `config/`, `public/` (`index.php`, `.htaccess`, `build/`, `bundles/`), `src/`, `templates/`, `translations/`, empty `var/`, `vendor/`. Secrets live only in the server's own `.env.local`, never upload the local one. After upload the prod cache must be cleared (`var/cache/prod`).

Schema changes: if `docs/sql/` gained files since the last deploy (`git diff --name-only <last deployed commit> HEAD -- docs/sql`), hand them to the user to run on the prod DB **before** the upload; the new code expects the new tables (e.g. a missing `championship_point` table breaks every page, since the sidebar VB module renders on all of them). Run them in file-name (date) order, over a utf8mb4 connection: scripts with accented data start with `SET NAMES utf8mb4;`, otherwise the MySQL CLI's latin1 default double-encodes them.

## Production Cron Jobs

- `/cron/collect_feeds` - Refresh f1vilag.hu news feed (every 15 min)
- `/cron/championship_result_cache_warmer` - Refresh championship results from RapidAPI into FileCache (every 15 min); lives in `src/Controller/Module/ResultsOfChampionshipController.php`, needs `RAPIDAPI_KEY` (not set locally), season = current year with fallback to the previous one before the first race

## Notes

- Read-only JSON endpoints for the Vue SPA live in `src/Controller/Api/ContentApiController.php` (`/api/events`, `/api/feeds`, `/api/information/{id}`); API Platform was removed
- Legacy code exists in `src/LegacyService/` namespace, being refactored incrementally
- The weekend place → championship point table lives in the `championship_point` table (entity `ChampionshipPoint`, admin menu "Bajnoki pontok", ROLE_SUPER_ADMIN), read through `PointProvider` from the second-level cache. Saving or deleting a row rescores every already calculated weekend from the stored weekend totals (`ChampionshipPointSubscriber` → `ChampionshipPointRecalculator`). The e2e seed loads the initial values (`E2eFixtures::POINT_TABLE`); hand-written prod schema changes go in `docs/sql/`
- The sidebar prize module ("Nyeremény", logged-in users only) comes from the single `prize_draw` row (entity `PrizeDraw`, id = `PrizeDraw::ID` = 1) and its `prize` rows (place → amount, plus a free-text note), edited in the admin "Nyeremény" menu (ROLE_SUPER_ADMIN; edit page only, new/list/delete disabled). With "Még nyitott a nevezés" (`entryOpen`) checked the module shows only the live count of `is_alter_champs` users (uncached `COUNT`). A missing row renders nothing (and the admin menu 404s, since "new" is disabled: re-run the `INSERT`s of the SQL). Place uniqueness is checked by the `PrizeDraw` validator, deliberately not a unique index (Doctrine inserts before it deletes, so re-adding or swapping places in one save would hit it). E2e seed: `E2eFixtures::PRIZES`, `PRIZE_NOTE`; prod SQL: `docs/sql/2026-11-23-prize-draw.sql`
