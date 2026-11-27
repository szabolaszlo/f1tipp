# Szezonváltás admin oldal – megvalósítási terv

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Admin → Karbantartás → „Szezonváltás” oldal: tiltásokkal és egyszer használható 6 jegyű kóddal védett szezonnullázás, valamint élő szezonváltási checklist.

**Architecture:** A DB-tényeket (darabszámok, évek) egy DBAL-alapú `SeasonFactsProvider` olvassa ki egy `SeasonFacts` DTO-ba. Ebből a tiszta `SeasonResetGuard` számolja a tiltásokat, a `SeasonChecklist` az élő pontokat. A `SeasonReset` egy tranzakcióban töröl, majd üríti a három cache-réteget. A `ConfirmationCode` kezeli a sessionben tárolt kódot. A felületet a `SeasonResetController` és egy EasyAdmin-layoutot öröklő Twig adja. A fő teszthálót a CodeceptJS e2e adja; unit teszt csak a tiszta logikára és a rollbackre készül.

**Tech Stack:** Symfony 6.4, Doctrine DBAL/ORM 2.20, EasyAdmin 4.29, Twig, CodeceptJS + Playwright (magyar Gherkin), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-27-season-reset-design.md`. Olvasd végig, mielőtt bármelyik taskba belekezdesz.

## Global Constraints

- **Az események (`event`) érintetlenek.** A nullázás nem módosíthatja és nem törölheti őket.
- **Töröl:** `bet_attribute`, `bet`, `result_attribute`, `result`, `alternative_championship`, `trophy`, ebben a sorrendben, `DELETE`-tel (**soha nem `TRUNCATE`**).
- **Userek:** `point_summary = 0`, `point_difference = NULL`, `alternative_point_summary = NULL`, `alternative_point_difference = NULL`.
- **Megmarad:** `driver` (a `point` is!), a userek sorai és `is_alter_champs` jelölése, `championship_point`, `prize_draw`, `prize`, `information`, `setting`, `message_wall`, `feed`.
- **Tranzakció:** minden egy DBAL-tranzakcióban fut, hiba esetén rollback, és a cache-ürítés ilyenkor nem fut.
- **Cache-ürítés a commit után:**
  - `$em->clear()`;
  - `evictEntityRegions()`, `evictCollectionRegions()`, `evictQueryRegions()`;
  - `ResultCache::of($em)->deleteAll()`;
  - `FileCache::clearAll()`, de a `ResultsOfChampionshipController::RESULTS_CACHE_KEY` bejegyzést előtte kiolvassuk, és ha volt, visszaírjuk. **(Terv-döntés:** különben az oldalsáv pilóta-VB-je élesen a cronig a RapidAPI-t hívja, e2e-ben pedig 503-at ad.)
- **`closingYear`** = `YEAR(MAX(event.date_time))` azon események közül, amelyekhez van `result`.
- **`currentYear`** = `YEAR(MAX(event.date_time))` az összes eseményből.
- **Tiltások:**
  - nincs mit nullázni (bet + result + trophy + alternative_championship = 0);
  - hiányzik a `slug = closingYear` Információ;
  - `mai év <= closingYear`;
  - van `result` jövőbeli dátumú eseményen.
- **A POST-nál a szerver mindent újraellenőriz.**
- **Kód:** 6 jegyű (`str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)`), session-kulcs `season_reset_code`. Minden GET-nél új. Minden POST után törlődik. Az összehasonlítás `hash_equals`-szal történik.
- **Hozzáférés:** `ROLE_SUPER_ADMIN` (`#[IsGranted]` mindkét route-on).
- **Route-ok:**
  - `season_reset` (GET) a `/admin/maintenance/season_reset` úton;
  - `season_reset_execute` (POST) a `/admin/maintenance/season_reset/execute` úton.
  - Az admin-kontextusban a `/admin?routeName=season_reset` URL-en érhető el (`MenuItem::linkToRoute`).
- **Szövegek (szó szerint, az e2e ezekre ellenőriz; `{Y}` = évszám):**
  - Menü és oldalcím: `Szezonváltás`
  - Tiltás, nincs adat: `Nincs mit nullázni.`
  - Tiltás, archív: `Hiányzik a „{Y}” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva.`
  - Tiltás, év: `A {Y}. évi szezon {Y+1}. január 1-jétől nullázható.`
  - Tiltás, jövőbeli eredmény: `Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át.`
  - Figyelmeztetés: `{n} tipp jövőbeli eseményre szól, ezek is törlődnek.`
  - Flash, rossz kód: `Hibás vagy lejárt megerősítő kód, semmi nem törlődött.`
  - Flash, CSRF: `Érvénytelen kérés, semmi nem törlődött.`
  - Flash, tiltott POST: `A nullázás most nem engedélyezett, semmi nem törlődött.`
  - Flash, siker: `Szezon nullázva: {b} tipp, {r} eredmény, {t} kupa, {a} alternatív pont-sor törölve, {u} felhasználó pontjai nullázva.`
- **DOM-horgok (e2e):**
  - `#season-reset-code` (a kiírt kód);
  - `#season-reset-code-input` (name=`code`), rejtett `_token` (CSRF id `season_reset`);
  - `#season-reset-submit`;
  - `#season-reset-blocks` (a tiltások listája, `li`-nként);
  - `#season-reset-counts` (a „törlődik” lista, soronként `[data-count="bet|result|trophy|alternative|user"]`);
  - checklist-pontok: `[data-check="<key>"][data-status="ok|fail|info|neutral|manual"]`.
- **phpunit nem írhat DB-be** (a fejlesztői `f1tippelek`-en fut). Futtatás: `docker exec f1tipp-php-1 php bin/phpunit`.
- **e2e:** `npm --prefix tests/e2e test -- --grep @season-reset`. A teljes suite: `npm run e2e`. Feltétel: fut a Docker és megvan a `npm run dev` build.

## Review Focus

1. **Duplakattintás vagy újraküldés a sikeres nullázás után.** Ugyanaz a kód másodszor → elutasítva, a közben bekerült új adat nem törlődik. Task 5, 16. forgatókönyv.
2. **Két nyitott fül:** az A fül kódja érvénytelen, miután a B fül betöltődött. Task 5, 15. forgatókönyv.
3. **Januári futtatás.** A „most”-hoz relatív seedek évet válthatnak. Az új szenáriók fix naptári dátumot használnak. Task 1 (a dátumok), Task 4 (az év-tiltás forgatókönyve).
4. **Részleges hiba a tranzakcióban.** Rollback, és nincs cache-ürítés. Task 3, unit teszt.
5. **A nullázás utáni első oldalbetöltések.** Nem lehet 5xx (pilóta-VB cache!), és nem látszhat régi pont a 2LC-ből. Task 5, 19. forgatókönyv.

---

### Task 1: E2e infrastruktúra (szenáriók, DB-számláló, lépések)

**Files:**
- Modify: `src/E2e/E2eScenarios.php`: új nevek a `NAMES`-ben, új ágak a `build()`-ben
- Create: `src/Command/E2e/E2eCountsCommand.php`
- Modify: `tests/e2e/helpers/E2eHelper.js`: `dbCounts()`
- Create: `tests/e2e/step_definitions/season_reset_steps.js`, `tests/e2e/pages/SeasonResetPage.js`
- Modify: `tests/e2e/codecept.conf.js`: a step fájl és a `seasonResetPage` include
- Modify: `tests/e2e/step_definitions/context.js`: ha kell, a `previous-season` eseménykulcsai

**Interfaces:**
- Produces:
  - **`previous-season`:** legyen `Y = idei év − 1`.
    - 1. hétvége „E2E 1. Nagydíj”: időmérő `Y-06-06 14:00`, futam `Y-06-07 14:00`. ctx-kulcsok: `qualify`, `race`, így a meglévő tipp- és eredménylépések működnek.
    - 2. hétvége „E2E 2. Nagydíj”: `Y-07-04 14:00` és `Y-07-05 14:00`. Kulcsok: `w2_qualify`, `w2_race`.
    - Mindkét hétvégéhez: `SEASON_BETS[0..1]` tippek, `Q`, illetve `Q` + `RACE_SAFETY_CAR` eredmény, eseményenként `calculate()`.
    - 3. hétvége „E2E Második Nagydíj”: most +7 nap / +8 nap. Kulcsok: `next_qualify`, `next_race`.
    - Információ: `slug = Y`, cím `Y`, tartalom `<p>E2E archív Y</p><img src="/build/images/trophy/trophy_gold.svg">`.
    - Egy üzenőfal-üzenet és egy `setting` sor (`e2eKey` = `e2eValue`), hogy a „megmarad” ellenőrizhető legyen.
  - **`previous-season-no-archive`:** ugyanez Információ nélkül.
  - **`previous-season-moved`:** ugyanez, de a `w2_race` dátuma most +3 nap.
  - **`current-season`:** időmérő és futam `max(most − 2 perc, idei év-01-01 00:00:00)`, illetve `max(most − 1 perc, …)` időponttal, tippekkel és eredménnyel. Plusz a 3. hétvége mint fent.
    - Ismert korlát: január 1. első perceiben a forgatókönyv nem futtatható.
  - **Meglévő szenáriók újrahasznosítva** (eltérés a spectől, ugyanazt fedik le):
    - `bets-placed` = „csak tippek, eredmény nincs”, múltbeli eseményeken;
    - `open-weekend` = „nincs mit nullázni”;
    - `others-bet` = „jövőbeli eseményre van tipp”.
  - **`app:e2e:counts`:** csak e2e env, `E2eGuard::assertE2e` + `assertE2eDatabase`. Egy JSON sort ír ki:

    ```json
    {"tables": {"bet":0,"bet_attribute":0,"result":0,"result_attribute":0,"alternative_championship":0,"trophy":0,
                "event":0,"user":0,"driver":0,"championship_point":0,"prize_draw":0,"prize":0,"information":0,
                "setting":0,"message_wall":0},
     "users": {"<username>": {"point_summary":0,"point_difference":null,"alternative_point_summary":null,
                              "alternative_point_difference":null,"is_alter_champs":true}},
     "drivers": {"<SHORT>": {"point":0,"status":true}},
     "events": {"<id>": "<Y-m-d H:i:s>"}}
    ```

  - **`I.dbCounts()`:** `Promise<object>`, a fenti JSON. A `_console(['app:e2e:counts'])` hívásra épül.
  - **Given lépések:**
    - `a tavalyi szezon lezárult, az archív bejegyzés megvan` → `previous-season`
    - `a tavalyi szezon lezárult, de nincs archív bejegyzés` → `previous-season-no-archive`
    - `a tavalyi szezon lezárult, de egy eredményes eseményt már jövőbe tettek` → `previous-season-moved`
    - `idei eredmény van egy lezajlott eseményen` → `current-season`
  - **Lépések:**
    - `megjegyzi az adatbázis állapotát` → `ctx.dbBefore = await I.dbCounts()`
    - `az adatbázis nem változott` → `deepStrictEqual` a `ctx.dbBefore`-ral

- [ ] **Step 1: Írd meg a szenáriókat és a parancsot**, a fenti értékekkel. A dátumok fix naptári dátumok (`new \DateTimeImmutable(sprintf('%d-06-06 14:00:00', $y))`), ne a „most”-hoz relatívak.
- [ ] **Step 2: Ellenőrizd kézzel.**
  - Futtasd: `docker compose exec -T -u www-data -w /var/www/f1tipp_sf4 php php bin/console app:e2e:scenario previous-season --env=e2e`, majd ugyanígy az `app:e2e:counts`-ot.
  - Elvárt: `bet` 12, `result` 4, `trophy` > 0, `alternative_championship` > 0, `information` tartalmazza az `Y` slugot, `message_wall` 1, `setting` ≥ 1.
  - Ugyanez a parancs `--env=dev`-vel → hiba, semmi nem fut.
- [ ] **Step 3: Futtasd a meglévő suite-ot**, hogy a megosztott fájlok (`context.js`, `E2eScenarios`) módosítása nem tört-e el semmit. Parancs: `npm run e2e`. Elvárt: minden zöld.
- [ ] **Step 4: Commit:** `git commit -m "Add e2e scenarios and a DB counts command for the season reset"`

---

### Task 2: Tiszta logika – SeasonFacts, SeasonResetGuard, ConfirmationCode

**Files:**
- Create: `src/Season/SeasonCounts.php`, `src/Season/SeasonFacts.php`, `src/Season/SeasonResetGuard.php`, `src/Season/ConfirmationCode.php`
- Test: `tests/Unit/Season/SeasonResetGuardTest.php`, `tests/Unit/Season/ConfirmationCodeTest.php`

**Interfaces:**
- Produces:
  - `final class SeasonCounts`: readonly int `bets`, `betAttributes`, `results`, `resultAttributes`, `trophies`, `alternatives`, `users`; plus `isEmpty(): bool` (bets + results + trophies + alternatives === 0).
  - `final class SeasonFacts`: readonly
    - `SeasonCounts $counts`
    - `?int $closingYear`, `?int $currentYear`
    - `bool $archiveExists`, `bool $alterArchiveExists`
    - `int $resultsOnFutureEvents`, `int $betsOnFutureEvents`
  - `final class SeasonResetGuard`:
    - `blockReasons(SeasonFacts $facts, \DateTimeImmutable $now): list<string>`: a Global Constraints szövegei, ebben a sorrendben: nincs adat (ha ez áll, csak ez az egy), archív, év, jövőbeli eredmény.
    - `warnings(SeasonFacts $facts): list<string>`: a jövőbeli tipp figyelmeztetés, ha `betsOnFutureEvents > 0`.
  - `final class ConfirmationCode` (a `RequestStack`-ből kéri a sessiont):
    - `issue(): string`
    - `consume(string $submitted): bool`: mindig törli a session-kulcsot; üres vagy hiányzó kódra `false`.

- [ ] **Step 1: Írd meg a teszteket.**
  - `SeasonResetGuardTest`:
    - `test_empty_season_only_reports_nothing_to_reset`: az `isEmpty` counts mellé hiányzó archív is jár → `['Nincs mit nullázni.']`.
    - `test_missing_archive_blocks`: `closingYear` 2025, `archiveExists` false, most 2026-02-01 → tartalmazza: `Hiányzik a „2025” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva.`
    - `test_same_year_blocks_until_new_year`: `closingYear` 2026, most 2026-12-15 → `A 2026. évi szezon 2027. január 1-jétől nullázható.`
    - `test_new_year_allows`: `closingYear` 2026, most `2027-01-01 00:00:00` → `[]`.
    - `test_result_on_future_event_blocks`: `resultsOnFutureEvents` 1 → a jövőbeli eredmény szövege.
    - `test_bets_only_needs_no_archive`: `closingYear` null, bets 5 → `[]`.
    - `test_future_bets_warn`: `betsOnFutureEvents` 3 → `warnings()` = `['3 tipp jövőbeli eseményre szól, ezek is törlődnek.']`.
  - `ConfirmationCodeTest` (`RequestStack` + `Session(new MockArraySessionStorage())`):
    - `test_issue_returns_six_digits`: `assertMatchesRegularExpression('/^\d{6}$/', …)`, 50 ismétlésben.
    - `test_consume_accepts_issued_code_once`: első `true`, második `false`.
    - `test_consume_rejects_wrong_code_and_invalidates`: rossz kód → `false`, utána a jó kód is `false`.
    - `test_new_issue_invalidates_previous`: A = issue, B = issue, consume(A) → `false`.
    - `test_consume_rejects_empty`: `consume('')` → `false`.
- [ ] **Step 2: Futtasd, hogy elbukjanak.** Parancs: `docker exec f1tipp-php-1 php bin/phpunit tests/Unit/Season`. Elvárt: FAIL, hiányzó osztályok.
- [ ] **Step 3: Implementáld a négy osztályt** a fenti szignatúrákkal.
- [ ] **Step 4: Futtasd újra** ugyanazt a parancsot. Elvárt: PASS.
- [ ] **Step 5: Commit:** `git commit -m "Add season reset guard and one-time confirmation code"`

---

### Task 3: SeasonFactsProvider és SeasonReset (DB + cache)

**Files:**
- Create: `src/Season/SeasonFactsProvider.php`, `src/Season/SeasonReset.php`
- Test: `tests/Unit/Season/SeasonResetTest.php` (csak mockolt kapcsolattal!)

**Interfaces:**
- Consumes: `SeasonCounts`, `SeasonFacts` (Task 2)
- Produces:
  - `SeasonFactsProvider::load(\DateTimeImmutable $now): SeasonFacts`. Csak DBAL `SELECT`-ek. A „jövőbeli” jelentése: `event.date_time > :now`.
  - `SeasonReset::reset(): SeasonCounts`. A nullázás előtti darabszámokat adja vissza; ezeket a tranzakción belül számolja.

- [ ] **Step 1: Írd meg a rollback-tesztet.**
  - `test_failure_rolls_back_and_skips_cache_clear`:
    - mock `Connection`, amelynek az `executeStatement` a 4. hívásra `RuntimeException`-t dob;
    - mock `EntityManagerInterface` (`getConnection()`), mock `FileCache`.
  - Elvárás:
    - `beginTransaction` egyszer, `rollBack` egyszer, `commit` soha;
    - `FileCache::clearAll` soha;
    - a kivétel kifelé továbbmegy (`expectException`).
  - `test_success_preserves_championship_cache`:
    - minden statement sikeres;
    - a `FileCache::get(RESULTS_CACHE_KEY)` `['x']`-et ad;
    - elvárás: `clearAll` egyszer, utána `save(RESULTS_CACHE_KEY, ['x'])`.
- [ ] **Step 2: Futtasd, hogy elbukjon.** Parancs: `docker exec f1tipp-php-1 php bin/phpunit tests/Unit/Season/SeasonResetTest.php`. Elvárt: FAIL.
- [ ] **Step 3: Implementáld.**
  - Explicit `beginTransaction`, `try { … commit } catch { rollBack; throw }`, nem `transactional()`, hogy a mock-teszt determinisztikus legyen.
  - A statementek sorrendje a Global Constraints szerinti. Utána jön a cache-ürítés.
  - `SeasonFactsProvider`: egyszerű COUNT/MAX lekérdezések. Az archív ellenőrzése: `SELECT 1 FROM information WHERE slug = ?`.
- [ ] **Step 4: Futtasd újra** ugyanazt a parancsot, majd a teljes phpunit-ot. Elvárt: PASS, és a régi 64 teszt is zöld.
- [ ] **Step 5: Commit:** `git commit -m "Add transactional season reset with full cache invalidation"`

---

### Task 4: Az oldal, a jogosultság, a darabszámok és a tiltások (GET)

**Files:**
- Create: `src/Controller/Admin/SeasonResetController.php` (csak a `season_reset` GET)
- Create: `templates/admin/season_reset.html.twig`, ami az `@EasyAdmin/page/content.html.twig`-et örökli
- Modify: `src/Controller/Admin/DashboardController.php:51-56`: `$submenu1`-be `MenuItem::linkToRoute('admin_maintenance_season_reset', 'fas fa-broom', 'season_reset')`
- Modify: `translations/messages.hu.xlf`: `admin_maintenance_season_reset` → `Szezonváltás`. A régi `admin_maintenance_reset_season*` kulcsok törölhetők.
- Create: `tests/e2e/features/season_reset.feature` (`# language: hu`, `@season-reset`)

**Interfaces:**
- Consumes: `SeasonFactsProvider::load`, `SeasonResetGuard`, `ConfirmationCode::issue` (Task 2–3)
- Produces: a DOM-horgok a Global Constraints szerint. A kód és az űrlap csak akkor jelenik meg, ha `blockReasons` üres. Az űrlap `action`-je: `ea_url().setRoute('season_reset_execute')`.

- [ ] **Step 1: Írd meg az e2e forgatókönyveket** (spec 1–4. és 7–12.).
  - A lépésszövegeket a meglévő stílusban írd; az ellenőrzött szövegek a Global Constraints szerintiek.
  - **1.** Vendég a `/admin?routeName=season_reset` URL-en → a belépés oldalra kerül.
  - **2.** `E2E Admin` (ROLE_ADMIN):
    - a Karbantartás menüben nincs „Szezonváltás”;
    - az oldal 403;
    - a böngészőből közvetlenül küldött POST 403 (`I.executeScript(fetch…)`, a `betting_steps.js:155` mintájára);
    - `az adatbázis nem változott`.
  - **3.** `E2E Superadmin`: a menüpont látszik, rákattintva megnyílik a „Szezonváltás” oldal.
  - **4.** `previous-season`: a `#season-reset-counts` értékei megegyeznek a `dbCounts()` `bet`, `result`, `trophy`, `alternative_championship`, `user` értékeivel. A „Megmarad:” sor látszik.
  - **7.** `previous-season-no-archive`:
    - látszik az archív-tiltás szövege (`Y` = tavaly);
    - nincs `#season-reset-submit`.
    - Ezután a szuperadmin a meglévő `felvesz egy új információs oldalt:` lépéssel felveszi az `Y` slugot, és újratölti a Szezonváltás oldalt → van `#season-reset-submit`.
  - **8.** `current-season`: az év-tiltás szövege (idei → jövő év), nincs `#season-reset-submit`.
  - **9.** `previous-season-moved`: a jövőbeli-eredmény tiltás szövege látszik.
  - **10.** `open-weekend`: `Nincs mit nullázni.`
  - **11.** `bets-placed`: nincs tiltás, van `#season-reset-submit`.
  - **12.** `others-bet`: `2 tipp jövőbeli eseményre szól, ezek is törlődnek.`, és van `#season-reset-submit`.
- [ ] **Step 2: Futtasd, hogy elbukjanak.** Parancs: `npm --prefix tests/e2e test -- --grep @season-reset`. Elvárt: FAIL (404 / hiányzó menüpont).
- [ ] **Step 3: Implementáld** a GET-et, a template nullázási blokkját (a checklist szakaszai Task 6-ban jönnek) és a menüt.
  - `#[IsGranted('ROLE_SUPER_ADMIN')]`.
  - A „most” `new \DateTimeImmutable()`.
- [ ] **Step 4: Futtasd újra** ugyanazt a parancsot. Elvárt: PASS.
- [ ] **Step 5: Commit:** `git commit -m "Add the season change admin page with reset guards"`

---

### Task 5: A nullázás végrehajtása (POST, kód, siker, a front utána)

**Files:**
- Modify: `src/Controller/Admin/SeasonResetController.php`: `season_reset_execute` POST
- Modify: `tests/e2e/features/season_reset.feature`, `tests/e2e/step_definitions/season_reset_steps.js`, `tests/e2e/pages/SeasonResetPage.js`

**Interfaces:**
- Consumes: `ConfirmationCode::consume`, `SeasonFactsProvider::load`, `SeasonResetGuard::blockReasons`, `SeasonReset::reset` (Task 2–3)
- Produces: a POST sorrendje:
  1. `consume($request->request->get('code', ''))`, mindig elsőként;
  2. CSRF (`season_reset`);
  3. `blockReasons` újra;
  4. `reset()`;
  5. flash;
  6. redirect `ea_url`-lel a `season_reset` route-ra.

  Az első hibánál a flash a megfelelő szöveg, és nincs `reset()`.

- [ ] **Step 1: Írd meg az e2e forgatókönyveket** (spec 7. POST-ága, 13–19., 22.). Minden elutasított esetben `megjegyzi az adatbázis állapotát` előtte, `az adatbázis nem változott` utána.
  - **7/POST.** `previous-season-no-archive`: a superadmin közvetlen POST-ot küld egy érvényes kóddal és CSRF-fel. Mindkettőt egy előtte megnyitott, tiltás nélküli oldalról olvasd ki: előbb seedeld és nyisd meg a `previous-season`-t, olvasd ki a kódot és a tokent, majd töröld az Információt az adminban, és küldd be. → `A nullázás most nem engedélyezett, semmi nem törlődött.`
  - **13.** Rossz kód (`000000`, ha épp nem az a kód; különben `111111`) → hibaflash, és a `#season-reset-code` más, mint előtte.
  - **14.** Üres kód → hibaflash.
  - **15.** Az oldal betöltése (A kód), újratöltés (B), majd az A beküldése közvetlen POST-tal, a mostani CSRF-fel → hibaflash.
  - **16.** Jó kód → siker. Utána ugyanaz a kód még egyszer, közvetlen POST-tal → hibaflash.
    - Előtte `E2E User1` tippel a `next_qualify`-ra a UI-n, és ez a tipp a második POST után is megvan (`dbCounts().tables.bet` = 1).
  - **17.** Közvetlen POST hamis `_token`-nel és jó kóddal → `Érvénytelen kérés, semmi nem törlődött.`
  - **18.** `previous-season`, jó kód → a siker-flash a `dbBefore` darabszámaival. `dbCounts()` szerint:
    - a 6 törlendő tábla 0;
    - minden user `point_summary` 0, a többi pontmező `null`;
    - **egyezik a `dbBefore`-ral:** `event`, `user`, `driver`, `championship_point`, `prize_draw`, `prize`, `information`, `setting`, `message_wall` darabszám, az `events` dátumai, a `drivers` pontjai és státusza, a `users.*.is_alter_champs`.
  - **19.** A nullázás után `E2E User1` belép:
    - a VB tabellában mindenki 0 ponttal, kupa nélkül (a meglévő `standings_steps.js` lépéseivel);
    - a Trófeák oldal üres;
    - az Eredmények oldalon az `Y` fülre kattintva: `E2E archív Y`;
    - `seeNoServerErrors` és `seeNoConsoleErrors`.
  - **22.** A nullázás után az oldal újratöltve: `Nincs mit nullázni.`
- [ ] **Step 2: Futtasd, hogy elbukjanak.** Parancs: `npm --prefix tests/e2e test -- --grep @season-reset`. Elvárt: az új forgatókönyvek FAIL, a Task 4-esek PASS.
- [ ] **Step 3: Implementáld a POST-ot.**
- [ ] **Step 4: Futtasd újra** ugyanazt a parancsot. Elvárt: PASS.
- [ ] **Step 5: Commit:** `git commit -m "Execute the season reset behind a one-time code"`

---

### Task 6: Élő checklist

**Files:**
- Create: `src/Season/ChecklistItem.php`, `src/Season/SeasonChecklist.php`
- Modify: `templates/admin/season_reset.html.twig`: a 4 szakasz a spec „Az oldal felépítése” része szerint
- Test: `tests/Unit/Season/SeasonChecklistTest.php`; e2e a `season_reset.feature`-ben

**Interfaces:**
- Consumes: `SeasonFacts` (Task 2)
- Produces:
  - `final class ChecklistItem`: readonly `string $key`, `string $status` (`ok|fail|info|neutral|manual`), `string $label`, `?string $detail`.
  - `SeasonChecklist::build(SeasonFacts $facts): array<string, list<ChecklistItem>>`. A szakaszkulcsok: `before_reset`, `new_season`, `after_first_race`.
  - Konstruktor: `Connection`, `#[Autowire('%f1tipp.teams%')] array $teams`, `#[Autowire('%env(default::RAPIDAPI_KEY)%')] ?string $rapidApiKey`.
  - `public static function findHashedImageUrls(string $html): list<string>`: regex `#/build/images/[^"'\s]+\.[0-9a-f]{8}\.(png|svg|jpe?g|gif|webp)#`.
  - `public static function driversWithoutTeam(list<string> $activeShorts, array $teams): list<string>`.
  - **Élő kulcsok:** `archive`, `hashed_images`, `alter_archive`, `stale_events`, `championship_points`, `prize`, `participants`, `drivers_without_team`, `rules_title`, `rapidapi_key`.
  - **Kézi kulcsok:** `backup`, `deploy_code`, `after_deploy`, `driver_sync`, `championship_sidebar`.
  - A szabályok a spec „Élő ellenőrzések pontos szabályai” táblája szerintiek. A `stale_events` szabálya: `date_time < now` és nincs `result`.
  - A template a kulcs alapján linkel, `ea_url()`-lel:
    - `archive` → Információ új;
    - `stale_events` → Event index;
    - `championship_points`, `prize` (PrizeDraw edit, id 1), `participants` (User index), `drivers_without_team` (Driver index);
    - `rules_title` → Information edit 23;
    - `driver_sync` → Driver index.

- [ ] **Step 1: Írd meg a unit teszteket.**
  - `test_finds_hashed_image_urls`:
    - a `<img src="/build/images/trophy_gold.c61f05af.png">` találat;
    - a `/build/images/trophy/trophy_gold.svg` nem.
  - `test_drivers_without_team`: `['VER','TSU']`, ahol a teams-ben csak VER szerepel → `['TSU']`.
  - `test_empty_rapidapi_key_fails`: a `build()` `rapidapi_key` itemje `fail` üres kulccsal. Mock `Connection`, amelynek a lekérdezései üres vagy 0 értéket adnak.
- [ ] **Step 2: Írd meg az e2e forgatókönyveket** (spec 5–6.). Mindegyiknél az elvárt `data-status`, és ahol van, a szöveg:
  - **archív:** `previous-season` → `ok`; `previous-season-no-archive` → `fail`.
  - **hash-es kép:** az archív tartalmát az adminban `<img src="/build/images/trophy_gold.c61f05af.png">`-re írja → `fail`, és a részletek között ott az URL. Alapból `ok`.
  - **alter:** alapból `info` „nincs”. A szuperadmin felvesz egy `alter-Y` slugú oldalt → `info` „létezik”.
  - **régi események:** `previous-season` → `ok`. A nullázás után → `fail` „4 esemény”. A meglévő `a hétvége minden eseményét {int} nappal későbbre teszi` lépéssel a jövőbe tolva → `fail` „2 esemény”.
  - **bajnoki pontok:** `info` `15-11-9-7-6-5-4-2-1-0`. A meglévő `átírja a bajnoki pontokat:` lépés után az új sor.
  - **nyeremény:** `info` „lezárva, 3 díjsor”. Bepipálás után „nyitva”.
  - **résztvevők:** `info` „3”. A `kiveszi a bajnokságból` lépés után „2”.
  - **pilóta csapat nélkül:** az e2e seedben aktív a `TSU`, `STR`, `OCO`, `HUL`. Ellenőrizd a `f1tipp_teams.yaml` ellen, mely kódok hiányoznak (a 2026-os listában `TSU` nincs benne). Elvárt: `fail`, a detailben a hiányzó kódok. A `TSU` inaktiválása után a lista nélküle.
  - **szabályzat címe:** a seedelt cím ellen (`E2eFixtures` id 23). Ha a `currentYear` benne van → `ok`, egyébként `fail`. Az adminban átírva a másikra vált.
  - **RAPIDAPI_KEY:** `ok`.
  - **linkek:** minden linkre kattintva a megfelelő EasyAdmin oldal nyílik meg (`I.seeInCurrentUrl` a `crudControllerFqcn`-re).
- [ ] **Step 3: Futtasd, hogy elbukjanak.** Unit: `docker exec f1tipp-php-1 php bin/phpunit tests/Unit/Season/SeasonChecklistTest.php`. E2e: `npm --prefix tests/e2e test -- --grep @season-reset`. Elvárt: FAIL.
- [ ] **Step 4: Implementáld** a `SeasonChecklist`-et és a template szakaszait. A kézi pontok szövege a spec vázlatából és az Obsidian checklistből jön.
- [ ] **Step 5: Futtasd újra** mindkét parancsot. Elvárt: PASS.
- [ ] **Step 6: Commit:** `git commit -m "Show the live season change checklist on the admin page"`

---

### Task 7: Élet a nullázás után, regresszió, dokumentáció

**Files:**
- Modify: `tests/e2e/features/season_reset.feature`: a spec 20–21. forgatókönyvei
- Modify: `CLAUDE.md` (Notes), `docs/superpowers/specs/2026-11-27-season-reset-design.md`: az „Eltérések” rész a Task 1-ben újrahasznosított szenáriókról és a pilóta-VB cache megőrzéséről

**Interfaces:**
- Consumes: minden korábbi task

- [ ] **Step 1: Írd meg a 20–21. forgatókönyvet.**
  - **20.** `previous-season`, a nullázás után:
    - a szuperadmin az 1. hétvége (`qualify`, `race`) időpontját a jövőbe teszi, most +2 óra és +1 nap;
    - `E2E User1` tippel az Időmérőre (`Q`) a UI-n;
    - az admin rögzíti az Időmérő eredményét (`Q`) a meglévő lépéssel;
    - `E2E User1` az Aktuális oldalon az időmérőre 40 pontot kap (10 × 4). A VB tabellát itt nem ellenőrizzük: az alternatív pont a futamhoz kötődik, a teljes hétvége-számolást a meglévő `@weekend` és `@season` feature-ök fedik le.
    - A `getNextEventForResult()` eredmény nélkül a legkisebb id-jű eseményt adja; ez az 1. hétvége időmérője, ezért ez működik.
  - **21.** A nullázás után az `az admin újraszámolja az összes pontot` lépés lefut. Utána a `dbCounts()` szerint a `trophy` és az `alternative_championship` 0, és nincs 5xx.
- [ ] **Step 2: Futtasd** a `npm --prefix tests/e2e test -- --grep @season-reset` parancsot, majd a **teljes** suite-ot (`npm run e2e`) és a phpunit-ot. Elvárt: minden zöld. A teljes suite-ra azért van szükség, mert a Task 1 megosztott fájlokat módosított.
- [ ] **Step 3: Dokumentáció.**
  - `CLAUDE.md` → Notes: egy sor az oldalról:
    - Admin → Karbantartás → „Szezonváltás”, ROLE_SUPER_ADMIN;
    - `src/Season/`;
    - a tiltások;
    - „events untouched”;
    - e2e `@season-reset`.
  - A specbe „Eltérések az eredeti tervtől” tábla.
- [ ] **Step 4: Commit:** `git commit -m "Cover life after the season reset and document it"`
- [ ] **Step 5 (merge után, a főszálon):**
  - Frissítsd az Obsidian „Évváltási checklist (szezonváltás)” jegyzetet: rövid TL;DR, hogy a checklist az admin Szezonváltás oldalán él; a háttér és a buktatók maradnak.
  - Az élesítési teendőkhöz írd be: az InnoDB-ellenőrzés egyszer az éles DB-n:
    `SHOW TABLE STATUS WHERE Name IN ('bet','bet_attribute','result','result_attribute','alternative_championship','trophy','user')`.
