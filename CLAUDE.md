# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

F1Tipp is a Formula 1 prediction/betting game application. It's a Symfony refactoring of a legacy custom framework project, hosted at https://f1tippelek.hu (Nethely; f1tipp.nhely.hu 301-redirects there).

## Tech Stack

**Backend:** Symfony 6.4 LTS, Doctrine ORM 2.20 (PHP attribute mapping), EasyAdmin 4, Google OAuth2
**Frontend:** Vue.js 3 (plain, SFC-only, no runtime template compiler), Vue Router 4, Vue I18n, Webpack Encore
**Database:** MySQL 5.6 (Docker)
**Infrastructure:** Docker Compose (Nginx, PHP-FPM 8.4, MySQL, PHPMyAdmin)

## Commands

### Setup
```bash
docker compose up -d        # v2 syntax; the old docker-compose binary isn't installed
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
**Rule: every feature or fix gets CodeceptJS e2e coverage for every scenario**: happy path, each guard or forbidden case, bad input, permissions, and what must NOT change. List the scenarios in the design doc. Unit tests only supplement cases e2e can't trigger. The user doesn't want surprises in prod.

```bash
npm run e2e                                          # all e2e, headless
npm --prefix tests/e2e run test:headed               # with a visible browser
npm --prefix tests/e2e test -- --grep @admin         # one feature (by its @tag)
docker exec -w /var/www/f1tipp_sf4 f1tipp-php-1 php bin/phpunit   # phpunit (mysql_db doesn't resolve on the host; the container's default workdir has no bin/phpunit)
```

- **e2e environment:** runs on the host (Node 22) against http://127.0.0.1:8001: nginx :81, `APP_ENV=e2e`, own DB `f1tipp_e2e` from `.env.e2e`.
  - Needs the Docker stack and built assets (`npm run dev` or a running `watch`).
- **Layout:** Hungarian Gherkin in `tests/e2e/features/*.feature` (one `@tag` per feature), steps in `step_definitions/`, page objects in `pages/`, and `helpers/E2eHelper.js`.
  - Register new step files and page objects in `codecept.conf.js`.
- **Seeding:** every Forgatókönyv starts from scratch with `I.seedScenario('<name>')` → `app:e2e:scenario` (`src/E2e/E2eScenarios.php`; base fixtures in `src/E2e/E2eFixtures.php`).
  - Users: `e2e_user`, `e2e_user2`, `e2e_user3` (ROLE_USER, in the championship), `e2e_admin` (ROLE_ADMIN), `e2e_superadmin` (ROLE_SUPER_ADMIN). Password: `e2e-pass`.
  - `app:e2e:*` refuses to run outside the `e2e` env.
  - Scenario dates are relative to "now". Use fixed calendar dates when the year matters (January!).
- **e2e rules:** preconditions may be seeded, but the step under test goes through the UI. Wait for state, not time.
  - External hosts are blocked. The helper only collects 5xx responses and console errors; a scenario fails on them only through the `nem volt szerverhiba` / `nincs böngészőhiba` steps, so end every scenario that loads front pages with them.
  - Never run two e2e runs at the same time (e.g. one feature while the full suite runs): they share the `f1tipp_e2e` DB and reseed it under each other.
- **phpunit runs against the dev DB `f1tippelek`** (`.env.test` has no `DATABASE_URL`, and there is no `dbname_suffix`). Never write data-modifying phpunit tests.

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
- **e2e app:** http://127.0.0.1:8001/ (`APP_ENV=e2e`, DB `f1tipp_e2e`)
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

Schema changes: every hand-written prod schema change not yet run on prod is collected in **one** file, `docs/sql/pending.sql`, in run order. A new change is appended to it as a new numbered section, never a separate file. Before the upload, hand `pending.sql` to the user to run on the prod DB in one go; the new code expects the new tables (e.g. a missing `championship_point` table breaks every page, since the sidebar VB module renders on all of them). It starts with `SET NAMES utf8mb4;`, otherwise the MySQL CLI's latin1 default double-encodes accented data. Once the user confirms it ran on prod, `git mv` it to `docs/sql/applied/<prod run date>.sql`; the next change starts a new `pending.sql`. Test it before handing over on a throwaway DB (`CREATE DATABASE …`, run it, check, `DROP DATABASE`).

## Production Cron Jobs

- `/cron/collect_feeds` - Refresh f1vilag.hu news feed (every 15 min)
- `/cron/championship_result_cache_warmer` - Refresh championship results from RapidAPI into FileCache (every 15 min); lives in `src/Controller/Module/ResultsOfChampionshipController.php`, needs `RAPIDAPI_KEY` (not set locally), season = current year with fallback to the previous one before the first race

## Domain rules

- **Bet points** (`src/Rule/RuleType/`):
  - Qualify p1–p10: exact place 4, in the top 10 at the wrong place 1.
  - Race: 3 / 1, plus the safety-car question 3.
  - SprintQualify p1–p8: exact place 1.
  - If these change, the Rules page text and the e2e expectations change too.
- **Scoring:** saving a result in the admin → `EasyAdminSubscriber` → `Calculator` (BetResult → Trophy → UserPointSummary → Alternative → AlternativePointSummary).
  - Results are entered in time order (`getNextEventForResult()`).
- **Sidebar "VB" table:** shows the alternative championship points (weekend place → `championship_point`), not the raw bet points.
  - Order: `UserChampionshipOrderResolver`.
  - Trophies only go to `is_alter_champs` users.
  - Season trophy points (gold/silver/bronze 25/18/15) live in `SeasonTrophiesController`.
- **Current weekend:** the `weekendOrder` of the nearest future event.
- **Results page:** the year tabs come from the browser clock.
  - The current year shows live data (`/module/results`).
  - Earlier years load the Information entry with slug `{year}` (`/information_content/{year}`).
  - Archive HTML may only use hash-free image URLs (`/build/images/trophy/…`).
- **Rules page:** Information id 23 (hard-coded in `Rules.vue`; never change the id).
- **Team colors and driver–team mapping:** `config/packages/f1tipp_teams.yaml`. Matched by the 3-letter `Driver.short`; the order of the entries matters.
- **Live updates:** `module_reload.js` polls `/module/entity_counts` every 10 s.
  - `/get_online_user` doubles as the online heartbeat; don't pause it on hidden tabs.
- **Admin:** `/admin` needs ROLE_ADMIN. Every CRUD except Results needs ROLE_SUPER_ADMIN (`setEntityPermission`).
  - Maintenance menu: point recalculation, cache clear.

## Caching (deliberate, don't "optimize" it away)

- **Three layers:**
  - the Doctrine second-level cache on every entity (`NONSTRICT_READ_WRITE`);
  - `FileCache` (rendered heavy modules, keys built from row counts);
  - `ResultCache` (`cache.app`).
- Admin saves clear everything through `EasyAdminSubscriber`.
- Any write outside it (DBAL or raw SQL) must clear all three itself: `$em->getCache()->evict*Regions()`, `FileCache::clearAll()`, `ResultCache::deleteAll()`.
- Don't flag N+1 or `findAll()` as a problem without measuring the warm and cold paths (the warm path is 0–1 SQL).

## UI conventions

- Red Bull palette:
  - `--f1-accent` (blue): section labels.
  - `--f1-select` (yellow): active or selected states.
  - Red only for the logo, primary buttons, errors and the P1 cell.
- Bet chips: purple = exact, green = near, yellow = miss.

## Project knowledge outside the repo

- Obsidian vault `Projects/F1Tipp/`: season-change checklist, prod deploy checklist, optimization backlog (with measurements).
- Designs: `docs/superpowers/specs/`.

## Notes

- Read-only JSON endpoints for the Vue SPA live in `src/Controller/Api/ContentApiController.php` (`/api/events`, `/api/feeds`, `/api/information/{id}`); API Platform was removed
- Legacy code exists in `src/LegacyService/` namespace, being refactored incrementally
- The weekend place → championship point table lives in the `championship_point` table (entity `ChampionshipPoint`, admin menu "Bajnoki pontok", ROLE_SUPER_ADMIN), read through `PointProvider` from the second-level cache. Saving or deleting a row rescores every already calculated weekend from the stored weekend totals (`ChampionshipPointSubscriber` → `ChampionshipPointRecalculator`). The e2e seed loads the initial values (`E2eFixtures::POINT_TABLE`); hand-written prod schema changes go in `docs/sql/pending.sql`
- The sidebar prize module ("Nyeremény", logged-in users only) comes from the single `prize_draw` row (entity `PrizeDraw`, id = `PrizeDraw::ID` = 1) and its `prize` rows (place → amount, plus a free-text note), edited in the admin "Nyeremény" menu (ROLE_SUPER_ADMIN; edit page only, new/list/delete disabled). With "Még nyitott a nevezés" (`entryOpen`) checked the module shows only the live count of `is_alter_champs` users (uncached `COUNT`). A missing row renders nothing (and the admin menu 404s, since "new" is disabled: re-run the `INSERT`s of the SQL). Place uniqueness is checked by the `PrizeDraw` validator, deliberately not a unique index (Doctrine inserts before it deletes, so re-adding or swapping places in one save would hit it). E2e seed: `E2eFixtures::PRIZES`, `PRIZE_NOTE`; prod SQL: section 2 of `docs/sql/pending.sql`
- The end-of-season reset lives on the admin page Admin → Karbantartás → "Szezonváltás" (ROLE_SUPER_ADMIN; code in `src/Season/`, controller `SeasonResetController`). It shows a live checklist and, only when allowed, a one-time 6-digit confirmation code plus the reset button; the POST burns the code first, then checks CSRF, then re-checks the guards. It is forbidden when there is nothing to reset, when the archive `Information` entry (slug = closing year) is missing, when the closing year is not over yet (resettable from 1 January of the next year), when a result belongs to a future-dated event (move the events only after the reset; then only this reason shows, since the closing year is unreliable), or when any of the reset tables is not InnoDB (unit-tested only). The reset deletes bets, results, trophies and alternative points in one transaction and zeroes the users' points, then clears the 2LC, FileCache (keeping the pilot-championship key) and the result cache. Events, users, drivers, `championship_point`, prizes, information, settings and messages stay untouched ("events untouched": the calendar is rewritten by hand afterwards). e2e: `@season-reset`; design: `docs/superpowers/specs/2026-11-27-season-reset-design.md`
