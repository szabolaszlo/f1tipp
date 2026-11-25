# Bajnoki pontozási tábla adatbázisból – implementációs terv

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** a hétvégi helyezés → bajnoki pont tábla a `championship_point` DB-táblából jöjjön, adminban szerkeszthető legyen, mentéskor a kiszámolt hétvégék újrapontozódjanak.

**Architecture:** új `ChampionshipPoint` entitás (2LC), a `PointProvider` a repositoryból olvas; a holtverseny-kezelés egy tiszta
`ChampionshipPointConverter`-be kerül (a meglévő `App\Display\CompetitionRanking`-re építve); EasyAdmin CRUD + egy
subscriber, ami mentés/törlés után a `ChampionshipPointRecalculator`-ral újrapontoz.

**Tech Stack:** Symfony 6.4, Doctrine ORM 2.20 (attribútumok, 2LC), EasyAdmin 4, PHPUnit 9, CodeceptJS + Playwright.

**Spec:** `docs/superpowers/specs/2026-11-23-championship-point-table-design.md`

## Global Constraints

- Mostani tábla (éles seed és e2e seed): 1→15, 2→11, 3→9, 4→7, 5→6, 6→5, 7→4, 8→2, 9→1, 10→0.
- Táblában nem szereplő helyezés = 0 pont; 0 gyűjtött hétvégi pont = 0 bajnoki pont; holtverseny = azonos pont, a következő helyezés ugrik.
- Nincs Doctrine migration: éles SQL-t a végén adunk, lokálisan is azzal alkalmazzuk. MySQL 5.6, InnoDB, utf8mb4_unicode_ci.
- A kupapontok (25/18/15) és a tippenkénti pontok nem változnak.
- A meglévő `.feature` fájlok változatlanok maradnak.

## Review Focus

- Üres tábla (minden sor törölve): minden bajnoki pont 0, nincs kivétel; az oldalsáv „hátralévő pontok” 0. → Task 1 unit teszt + Task 2 `getPlacePoint` 0-t ad.
- Duplikált helyezés mentése: validációs hiba, nem DB-kivétel (500). → Task 3 `UniqueEntity`.
- Negatív pont / 0. helyezés: validációs hiba. → Task 3 `Assert`.
- Több résztvevő, mint helyezés a táblában: a táblán kívüliek 0 pontot kapnak. → Task 1 unit teszt.
- Mentés után a régi tábla nem ragadhat be cache-ből (2LC query cache, oldalsáv-sorrend, FileCache). → Task 3 subscriber evict + e2e ellenőrzés.

---

### Task 1: ChampionshipPointConverter (tiszta átváltás)

**Files:** Create `src/Calculator/ChampionshipPointConverter.php`, Test `tests/Unit/Calculator/ChampionshipPointConverterTest.php`

**Interfaces:** Produces `ChampionshipPointConverter::convert(array $collectedPoints, array $placePoints): array` —
`$collectedPoints` csökkenő sorrendű hétvégi összpontok (list<int>), `$placePoints` helyezés => pont; azonos indexű bajnoki pontok.

- [ ] Tesztek: mostani tábla [70, 67, 15] → [15, 11, 9]; egyedi tábla (25/18/15) [70, 67, 15] → [25, 18, 15]; holtverseny [70, 70, 15] → [15, 15, 9]; 0 pont [70, 0, 0] → [15, 0, 0]; több résztvevő, mint hely (tábla 1→3, 2→1) [9, 8, 7] → [3, 1, 0]; üres tábla → minden 0; üres bemenet → [].
- [ ] Implementáció: `CompetitionRanking::rank()` + `$points > 0 ? ($placePoints[$rank] ?? 0) : 0`.
- [ ] `php bin/phpunit tests/Unit/Calculator` zöld, commit.

### Task 2: Entitás, repository, PointProvider DB-ből, számolás és megjelenítés átkötése

**Files:** Create `src/Entity/ChampionshipPoint.php`, `src/Repository/ChampionshipPointRepository.php`, `docs/sql/2026-11-23-championship-point.sql`;
Modify `src/Calculator/Provider/PointProvider.php`, `src/Calculator/Type/AlternativeCalculator.php`, `src/Twig/PointCalculatingInfoRuntimeExtension.php`,
`src/Twig/UserChampionshipTableRuntimeExtension.php`, `templates/extension/point_calculating_info.html.twig`, `src/E2e/E2eFixtures.php`

**Interfaces:**
- `ChampionshipPoint`: `id`, `place` (int, unique), `points` (int); getterek/setterek; `#[ORM\Cache('NONSTRICT_READ_WRITE')]`, tábla `championship_point`.
- `ChampionshipPointRepository::getPlacePoints(): array<int,int>` — cacheable query, place szerint növekvő.
- `PointProvider(ChampionshipPointRepository)`: `getPlacePoint(int $place): int`, `getPlacePoints(): array` (API változatlan, konstansok törölve).
- `AlternativeCalculator` a convertert használja; `E2eFixtures::POINT_TABLE` (mostani tábla) seedelve a `loadBase()`-ben.

- [ ] SQL fájl megírása, lokális DB-n lefuttatni, `doctrine:schema:update --dump-sql` ne mutasson eltérést a `championship_point`-ra.
- [ ] Átkötés, a Twig-extensionök DI-ből kapják a providert; a pontinfó `colspan` = helyezések száma + 1, a fix „12 résztvevő X 5 = 60 pont” helyett „Összpont = {{ összeg }} pont”.
- [ ] Ellenőrzés: `php bin/phpunit` zöld, e2e `weekend.feature` és `season_fix.feature` zöld (változatlan elvárásokkal). Commit.

### Task 3: Admin CRUD + újrapontozás mentéskor

**Files:** Create `src/Calculator/ChampionshipPointRecalculator.php`, `src/Controller/Admin/ChampionshipPointCrudController.php`, `src/EventSubscriber/ChampionshipPointSubscriber.php`;
Modify `src/Controller/Admin/DashboardController.php`, `src/Repository/AlternativeChampionshipRepository.php`, `translations/messages.hu.xlf`

**Interfaces:**
- `AlternativeChampionshipRepository::getCalculatedRaces(): Race[]` (azok a futamok, amelyekhez van sor).
- `ChampionshipPointRecalculator::recalculate(): int` — minden kiszámolt hétvégére converter a tárolt `collectedPoints`-ra, flush, majd `AlternativePointSummaryCalculator::calculate()`; visszaadja az újrapontozott hétvégék számát.
- Subscriber: `AfterEntityPersisted/Updated/Deleted` → ha `ChampionshipPoint`: 2LC query/entity régiók ürítése, `recalculate()`, `FileCache::clearAll()`, flash `flash_championship_points_recalculated` (%count%).
- CRUD: ROLE_SUPER_ADMIN, mezők `place`, `points`, rendezés place ASC, help szöveg a kiszámolt hétvégék számával; validáció `UniqueEntity(place)`, `Positive(place)`, `PositiveOrZero(points)`.
- Menü: `admin_championship_points`, ikon `fas fa-list-ol`.

- [ ] Megvalósítás, cache:clear, kézi próba lokálon (szerkesztés → flash, oldalsáv frissül).
- [ ] Commit.

### Task 4: E2E forgatókönyvek

**Files:** Create `tests/e2e/features/point_table.feature`; Modify `tests/e2e/step_definitions/admin_steps.js`, `tests/e2e/step_definitions/standings_steps.js`, `src/E2e/E2eScenarios.php` (új `results-tie`), `tests/e2e/step_definitions/common_steps.js`

Forgatókönyvek:
- Lezárt hétvége után P1–P3 átírása 25/18/15-re → flash „1 kiszámolt hétvége…”, összesítő és VB tabella 25/18/15, a pontinfó-modul és a „hátralévő pontok” az új P1-et mutatja.
- Szezon (3 hétvége) után P1 átírása → mindhárom hétvége újrapontozva, VB tabella összegei az új szerint.
- Eredmény előtt átírt tábla (`bets-placed`) → az admin eredményrögzítése már az új táblával számol.
- Holtverseny (`results-tie`) új táblával → a holtversenyesek azonos pontot kapnak, a következő ugrik.
- P3 törlése → a 3. helyezett 0 bajnoki pontot kap; duplikált helyezés mentése validációs hibát ad.

- [ ] Megírás, `npm run e2e` teljes futás zöld. Commit.

### Task 5: Dokumentáció

- [ ] CLAUDE.md: a pontozási tábla helye (admin „Bajnoki pontok”), az éles SQL fájl. Commit.
