# F1Tipp (Symfony 4)
www.f1tipp.nhely.hu

This project is a refactoring of original f1tipp. (https://github.com/szabolaszlo/f1tipp)

The original project based on own framework, I decide to change it to symfony.
So there is some legacy code from the original code, like src\LegacyService, but If I have time I will refactoring it one by one.

Used technologies:
- PHP 8.4 (Docker: `.docker/php84/`)
- Symfony 4.4 https://symfony.com/doc/current/setup.html
- WebPack https://symfony.com/doc/current/frontend.html

# Install
```
docker-compose up -d
npm install
npm run watch
```
## PHPMyAdmin
http://localhost:8080/
root - root

## Localhost
http://127.0.0.1/
pointer to public directory in repository

# Database
### Create DataBase
```
php bin/console doctrine:database:create
```
### Create Schema
```
php bin/console doctrine:database:create
```

# E2E tesztek (CodeceptJS + Playwright)

A tesztek böngészőben kattintják végig az appot, egy külön `e2e` Symfony környezeten
(`http://127.0.0.1:8001`, nginx 81-es server blokk, `.env.e2e`). Ez a környezet külön
`f1tipp_e2e` adatbázist és saját cache-t használ, a dev adatokat nem érinti. Minden teszt nulláról
építi fel a saját adatait (`app:e2e:scenario`): teszt-userek (`e2e_user`, `e2e_user2`, `e2e_user3`,
`e2e_admin` – ROLE_ADMIN, `e2e_superadmin` – ROLE_SUPER_ADMIN; jelszó: `e2e-pass`), a mostani időponthoz
relatív események, tippek, eredmények.

### Előfeltétel
```
docker compose up -d          # a web konténer a 8001-es portot is kiajánlja
npm run dev                   # buildelt assetek (vagy fusson a watch)
cd tests/e2e && npm install && npx playwright install chromium
```

### Futtatás
```
npm run e2e                                   # teljes csomag, headless
npm --prefix tests/e2e run test:headed        # látható böngészővel
npm --prefix tests/e2e test -- --grep @scoring   # csak egy csoport, lásd lent
E2E_BASE_URL=http://127.0.0.1:8001 npm run e2e   # más cím esetén
npm --prefix tests/e2e test -- --features     # csak a Gherkin forgatókönyvek
npm --prefix tests/e2e test -- --tests        # csak a JS specek
```

A tesztek kétféle formában vannak:
- **Gherkin forgatókönyvek** (magyarul, `# language: hu`): `tests/e2e/features/*.feature`. A tippek és a várt
  pontok a forgatókönyvek táblázataiban vannak; a lépések megvalósítása `tests/e2e/step_definitions/`, ami
  a `tests/e2e/pages/` oldal-objektumaira épül. Jelenleg: a sprintes, 3 eseményes hétvége (`@sprint`).
- **JS specek** (CodeceptJS `Feature`/`Scenario`): `tests/e2e/specs/*_test.js`, a tesztadat és a várt pontok
  a `tests/e2e/data/expected.js`-ben.

Kézi seedelés (csak `--env=e2e`-vel fut, más környezetben hibával kilép):
```
docker compose exec -u www-data -w /var/www/f1tipp_sf4 php php bin/console app:e2e:scenario open-weekend --env=e2e
```
Szenáriók (2 eseményes hétvége: időmérő + futam): `open-weekend`, `deadline-passed`, `about-to-close`,
`bets-placed`, `results-entered`, `others-bet`, `others-bet-closed`, `race-closer`, `recent-weekend`,
`finished-weekend`, `results-without-user3`, `season` (3 lezajlott hétvége eredménnyel + egy következő).
Sprintes, 3 eseményes hétvége (sprint + időmérő + futam): `sprint-open-weekend`, `sprint-results-entered`.
A JS specek várt pontszámai és tesztadata: `tests/e2e/data/expected.js`; a Gherkin forgatókönyveké a `.feature` fájlokban.

Csoportok (`--grep`):
- `@login` belépés, kilépés, session; `@betting` tippleadás; `@deadline` határidő; `@display` mentett tipp
- `@visibility` mások tippjei csak saját tipp (vagy a határidő) után láthatók
- `@order` hétvégi sorrend (a legközelebbi esemény elöl, összesítő, váltás a következő hétvégére)
- `@scoring` eredmény és pontszámítás; `@fix` eredmény javítása / törlése (2 eseményes hétvége)
- `@sprint` (Gherkin: `features/sprint_weekend.feature`) sprintes, 3 eseményes hétvége végig: tippleadás
  3 userrel, eredmények eseményenként, hétvégi összesítő, tabella, kupák, rekordok, majd sprint / időmérő
  javítás és sprint eredmény törlése
- `@season` késői javítás több hétvégével később (tabella, kupák, holtverseny)
- `@admin` minden admin menüpont hatása a front oldalon; `@security` jogosultságok, tipp-felülírás, XSS
- `@live` élő modulfrissítés két böngészővel; `@pages` oldalak, statisztika, hírek, online jelzés; `@smoke`, `@helper`

# Project Production Details

## Cron
- `/cron/search_cover_photo` This cron is try to check new cover image from facebook/boxutca. Recommend refresh hourly.
- `/cron/collect_feeds` This cron is try to refresh news with the f1vilag.hu feed (https://f1vilag.hu/feed/). Recommend refersh every 15 minutes.
- `/cron/championship_result_cache_warmer` This cron is try to refresh results of championship. Recommend refersh every 15 minutes.
