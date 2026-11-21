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

## Production Cron Jobs

- `/cron/collect_feeds` - Refresh f1vilag.hu news feed (every 15 min)
- `/cron/championship_result_cache_warmer` - Refresh championship results from RapidAPI into FileCache (every 15 min); lives in `src/Controller/Module/ResultsOfChampionshipController.php`, needs `RAPIDAPI_KEY` (not set locally), season = current year with fallback to the previous one before the first race

## Notes

- Read-only JSON endpoints for the Vue SPA live in `src/Controller/Api/ContentApiController.php` (`/api/events`, `/api/feeds`, `/api/information/{id}`); API Platform was removed
- Legacy code exists in `src/LegacyService/` namespace, being refactored incrementally
