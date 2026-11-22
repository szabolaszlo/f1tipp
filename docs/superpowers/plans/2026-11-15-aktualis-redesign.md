# Aktuális oldal „TV-grafika” redesign – Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Az Aktuális oldal, az Eredmények oldal táblái és a közös keret (fejléc, oldalsáv) átállítása a jóváhagyott sötét, visszafogott „TV-grafika” dizájnra, mobile first, az adatlogika és az élő frissítés érintetlenül hagyásával.

**Architecture:** „A irány”: csak markup és CSS. A Twig sablonok (`templates/extension/**`, `templates/controller/module/**`) új BEM-szerű `f1-*` osztályokat kapnak, egy új `assets/css/theme.css` adja a tokeneket és komponenseket a Bootstrap 3 + `app.css` után betöltve. Három kis PHP helper (csapatszín, holtversenyes helyezés, jelmagyarázat-pontok) Twig függvényként érhető el. A Vue komponensek (Navigation, ChampionshipResult, News) ugyanazokat az osztályokat használják.

**Tech Stack:** Symfony 5.4 / Twig, PHP 7.4, PHPUnit 9.5, Vue 3 (@vue/compat), Webpack Encore, Bootstrap 3.3.7 (marad), Chart.js.

**Spec:** `docs/superpowers/specs/2026-11-15-aktualis-redesign-design.md` · Látványterv: `docs/superpowers/specs/2026-11-15-aktualis-redesign-mockup.html` (v11 – a CSS értékek forrása)

## Global Constraints

- Branch: `design27`. Minden commit ide megy.
- Tokenek (verbatim a spec/mockup alapján): háttér `#14141b`, panel `#1b1b24`, sor `#1f1f29`, vonal `#2a2a35`, chip `#24242f`, szöveg `#e6e6ec`, piros `#e10600`, pontos `#3ecf8e`, közel `#d9b44a`. Font: Titillium Web 400/600/700/900 (Google Fonts).
- Chip jelölés (verbatim): `.f1-chip--exact { border-color: rgba(62,207,142,.36); color: #a6d6bf; background: rgba(62,207,142,.04); }`, `.f1-chip--near { border-color: rgba(217,180,74,.3); border-style: dashed; color: #bcae86; }`, nem talált: `color: #686876`.
- Csapatszín CSAK valódi pilótán/csapaton (eredmény sor chip, pilóta-VB, konstruktőr-VB). Tippelő sorban és tippelő tippjén SOHA.
- Dobogós helyezésszín (`#d9b44a`, `#b9b9c4`, `#c08655`) csak hétvégi összesítőben és egyéni pontversenyben; tipptáblában nem.
- Belépett felhasználó sora nincs kiemelve.
- Nincs fül, nincs lenyitható rész; minden tábla egymás alatt, a mostani sorrendben.
- `.reload-able-module` wrapperek és `data-module-*` attribútumaik változatlanok (`templates/base.html.twig`, `templates/controller/module/actual.html.twig`).
- Pontszámítás, sorrend, kupa- és matek-logika nem változik (PHP oldalon csak új, megjelenítési helperek).
- Mobil: 360 px-től nincs vízszintes görgetés; töréspont 900 px; desktop grid `minmax(0,1fr) 380px`, max-width 1280 px.
- Tesztfuttatás: `docker exec -w /var/www/f1tipp_sf4 f1tipp-php-1 vendor/bin/phpunit <path>`; asset build: `npm run dev` (hoston); app: http://127.0.0.1:8000/

## Review Focus

1. **Esemény, amire még senki nem tippelt** (`only_users`, üres `bets`) → „Még senki nem tippelt” szöveg, nem üres táblaváz. Teszt: Task 4 `test_only_users_without_bets_shows_nobody_text`.
2. **Ismeretlen pilótakód** (pl. tartalék pilóta `DOO`, nincs a konfigban) → semleges szürke `#6b6b78`, nincs kivétel. Teszt: Task 3 `test_unknown_driver_gets_neutral_color`.
3. **Sprinthétvége** (8 pozíció, low point 0) → 8 oszlopos chip-rács, jelmagyarázatban nincs „rossz helyen” elem. Teszt: Task 4 `test_sprint_table_has_eight_columns_and_no_near_legend`.
4. **Hosszú felhasználónév 360 px-en** → ellipszis, nincs vízszintes görgetés. Ellenőrzés: Task 8 Playwright `scrollWidth <= clientWidth` 360 px-en, a `.f1-name` `text-overflow: ellipsis`.
5. **Nem belépett látogató** → fejlécben „Belépés” gomb, Aktuális oldal hibátlan. Ellenőrzés: Task 2 és Task 8 Playwright kijelentkezett állapotban.

---

### Task 1: Téma alap – tokenek, layout, Bootstrap-illesztés

**Files:**
- Create: `assets/css/theme.css`
- Modify: `assets/js/app.js` (a `require('../css/app.css')` után `require('../css/theme.css')`)
- Modify: `templates/base.html.twig` (fejléc: Google Fonts link, inline `<style>` háttérképes body szabály törlése; törzs: `container-fluid/row/col-*` helyett `.f1-layout > .f1-main + .f1-aside`; lábléc `.f1-footer`)

**Interfaces:**
- Produces (CSS osztályok, minden további task ezekre épít):
  - layout: `.f1-layout`, `.f1-main`, `.f1-aside`, `.f1-footer`
  - panel: `.f1-board`, `.f1-board__head`, `.f1-board__tag`, `.f1-board__title`, `.f1-board__meta`, `.f1-board__colhead`, `.f1-board__note`, `.f1-board__body`
  - sor: `.f1-row`, `.f1-pos`, `.f1-pos--p1|--p2|--p3`, `.f1-stripe` (inline `background`), `.f1-name` (ellipszis), `.f1-name small`, `.f1-nums`, `.f1-num`, `.f1-num--main`, `.f1-num--dim`
  - tipp: `.f1-tiprow`, `.f1-tiprow--head`, `.f1-tiprow--result`, `.f1-chips` (inline `--cols: N`), `.f1-chip`, `.f1-chip--exact`, `.f1-chip--near`, `.f1-chip--res` (inline `border-left-color`), `.f1-chip--q` (kérdés/SC chip), `.f1-legend`
  - egyéb: `.f1-trophies`, `.f1-trophy` (+ `b` a számnak), `.f1-stat` (nagy szám + címke), `.f1-muted`
- A `.f1-chips` rács: `grid-template-columns: repeat(var(--cols), minmax(0,1fr))`, a `.f1-chip--q` oszlop `minmax(0,1.15fr)` – a `--cols` a pozíciók száma, a kérdés chip a sablonban külön `--q: 1` változóval jelzett (`grid-template-columns: repeat(var(--cols), minmax(0,1fr)) repeat(var(--q,0), minmax(0,1.15fr))`).
- Bootstrap-illesztő réteg ugyanebben a fájlban, `/* compat */` szekcióban: `.panel`, `.panel-heading`, `.table`, `.table-striped`, `.form-control`, `.btn`, `.select2-*`, `.color-one/.color-two` sötét témára (`.color-one` → `#e6e6ec` félkövér, `.color-two` → `#9a9aa8`).

- [ ] **Step 1: theme.css megírása** a mockup `<style>` blokkja alapján, a fenti osztálynevekre átnevezve; mobil szabályok `@media (max-width: 900px)`. A tipp-sor mobilon: `grid-template-areas: "pos name pts" "chips chips chips"`; desktopon `grid-template-columns: 40px 130px 1fr 52px`.
- [ ] **Step 2: base.html.twig layout átírása.** A `#news` (desktop) a `.f1-main` végén, a `#news-on-mobile` + `visible-xs` duplikáció megszűnik: egyetlen `#news` a `.f1-main` végén (mobilon így is a fő tartalom után jön). `main.js`-ből a `#news-on-mobile` mount törlése.
- [ ] **Step 3: Build** – Run: `npm run dev` · Expected: `webpack compiled successfully`
- [ ] **Step 4: Ellenőrzés Playwrighttal** 375×812 és 1366×900: `/#/actual`, `/#/betting`, `/#/results`, `/#/calendar`, `/#/rules`, `/#/statistics`, `/#/login` – sötét háttér, nincs háttérkép, `document.documentElement.scrollWidth <= innerWidth`, a Tippelés űrlap mezői olvashatók (világos szöveg sötét mezőn).
- [ ] **Step 5: Commit** – `git add assets/css/theme.css assets/js/app.js assets/js/main.js templates/base.html.twig && git commit -m "Add dark theme foundation and grid layout"`

### Task 2: Fejléc és navigáció (Navigation.vue)

**Files:**
- Modify: `assets/js/components/Navigation.vue` (template + scoped style; a script logika – `isLogged`, `username`, `componentLoading` – marad; a jQuery collapse/`hambi-button` hivatkozás törlése)

**Interfaces:**
- Consumes: Task 1 tokenek.
- Produces: `.f1-header` (sticky, `#14141b`, alsó vonal `#2a2a35`), logó `F1<span>TIPP</span>` (span `#e10600`), desktop `nav` (≥900 px), mobil vízszintesen görgethető `.f1-mnav` (<900 px), aktív link alsó 3 px piros vonal (`router-link-active`).

- [ ] **Step 1: Template átírása.** Menüpontok sorrendje változatlan (Tippelés, Aktuális, Eredmények, Naptár, Szabályok, Statisztika). Jobb oldalon: kijelentkezve „Belépés” router-link; belépve avatar-kör kezdőbetűvel + név (mobilon 10 karakter után `…`) + kilépés ikon (`/logout`). Mobilon nincs hamburger-menü: a menü az alatta lévő görgethető sorban van, a felhasználó-blokk a logó sorában jobbra.
- [ ] **Step 2: `routerLinkClicked()`** csak `window.scrollTo(0, 0)`.
- [ ] **Step 3: Build + Playwright** 375 és 1366 px-en, kijelentkezve és belépve: menülinkek navigálnak, aktív jelölés helyes, nincs vízszintes görgetés.
- [ ] **Step 4: Commit** – `git commit -am "Restyle navigation header"`

### Task 3: Megjelenítési helperek (TDD)

**Files:**
- Create: `config/packages/f1tipp_teams.yaml` – paraméter `f1tipp.teams`
- Create: `src/Display/TeamColorProvider.php`
- Create: `src/Display/CompetitionRanking.php`
- Create: `src/Display/BetLegendProvider.php`
- Create: `src/Twig/DisplayExtension.php`
- Test: `tests/Unit/Display/TeamColorProviderTest.php`, `tests/Unit/Display/CompetitionRankingTest.php`, `tests/Unit/Display/BetLegendProviderTest.php`

**Interfaces:**
- `f1tipp.teams` formátum: lista `{ name: string, match: string[], color: string, drivers: string[] }`. 2026-os rajtrács: McLaren `#FF8000` [NOR, PIA]; Ferrari `#E8002D` [LEC, HAM]; Red Bull `#3671C6` [VER, HAD]; Mercedes `#27F4D2` [RUS, ANT]; Aston Martin `#229971` [ALO, STR]; Alpine `#0093CC` [GAS, COL]; Williams `#64C4FF` [ALB, SAI]; Racing Bulls `#6692FF` [LAW, LIN]; Haas `#B6BABD` [OCO, BEA]; Audi `#52E252` [HUL, BOR] (match: audi, sauber, kick); Cadillac `#C8C8C8` [PER, BOT]. `match` = kisbetűs részsztringek, amikkel az API csapatneve egyeztethető (pl. `["red bull"]`, de Racing Bullsnál `["racing bulls","rb f1","vcarb"]` – a Racing Bulls bejegyzés a Red Bull ELŐTT legyen, mert az első találat nyer).
- `App\Display\TeamColorProvider::__construct(array $teams)` (services.yaml: `$teams: '%f1tipp.teams%'`)
  - `colorForDriver(string $code): string` – kód nagybetűsítve; ismeretlen → `TeamColorProvider::NEUTRAL = '#6b6b78'`
  - `colorForTeam(string $apiTeamName): string` – kisbetűs részsztring-egyezés a `match` listákon, sorrendben; nincs → `NEUTRAL`
- `App\Display\CompetitionRanking::rank(array $scores): array` – bemenet: már csökkenő sorrendű pontszámok listája; kimenet: azonos indexű helyezések „standard competition” szerint (`[31,25,24,24,21] → [1,2,3,3,5]`).
- `App\Display\BetLegendProvider::__construct(RuleRegistry $registry)`; `forEventType(string $type): array` → `['exact' => int, 'near' => int, 'question' => ?int, 'positions' => int]` – `exact`/`near` az első `driver` típusú attribútum high/low pontja, `question` az első `question` típusú attribútum high pontja (vagy `null`), `positions` a `driver` attribútumok száma.
- Twig (`DisplayExtension`, lazy runtime nélkül, sima függvények): `team_color(code)`, `team_color_by_name(name)`, `competition_ranks(scores)`, `bet_legend(eventType)`.

- [ ] **Step 1: Failing tesztek**
  - `TeamColorProviderTest`: `test_known_driver_returns_team_color` (`VER` → `#3671C6`, `ver` → `#3671C6`); `test_unknown_driver_gets_neutral_color` (`DOO` → `#6b6b78`); `test_team_name_matches_substring` (`"McLaren Racing"` → `#FF8000`, `"Visa Cash App RB F1 Team"`/`"Racing Bulls"` → `#6692FF`, `"Red Bull Racing"` → `#3671C6`); `test_unknown_team_gets_neutral_color`. A tesztek a valódi `config/packages/f1tipp_teams.yaml`-t töltik be `Symfony\Component\Yaml\Yaml::parseFile`-lal.
  - `CompetitionRankingTest`: `test_ranks_with_ties` (`[31,25,24,24,21,19,18,18,10,0,0]` → `[1,2,3,3,5,6,7,7,9,10,10]`); `test_empty_input_returns_empty`.
  - `BetLegendProviderTest` (valódi `RuleRegistry` a konténerből, `KernelTestCase`): race → `['exact'=>3,'near'=>1,'question'=>3,'positions'=>10]`; qualify → `['exact'=>4,'near'=>1,'question'=>null,'positions'=>10]`; sprint_qualify → `['exact'=>1,'near'=>0,'question'=>null,'positions'=>8]`.
- [ ] **Step 2: Futtatás** – `docker exec -w /var/www/f1tipp_sf4 f1tipp-php-1 vendor/bin/phpunit tests/Unit/Display` · Expected: FAIL (osztályok nem léteznek)
- [ ] **Step 3: Implementáció** (konfig, három osztály, `DisplayExtension`, services.yaml bind)
- [ ] **Step 4: Futtatás** – ugyanaz · Expected: OK (minden teszt zöld)
- [ ] **Step 5: Commit** – `git add config src/Display src/Twig/DisplayExtension.php tests/Unit && git commit -m "Add display helpers: team colors, competition ranking, bet legend"`

### Task 4: Eseménytáblák – full / only_bets / only_users

**Files:**
- Modify: `templates/extension/result_table/type/full.html.twig`, `only_bets.html.twig`, `only_users.html.twig`
- Create: `templates/extension/result_table/_board_head.html.twig` (közös fejléc: tag = `('general_' ~ type)|trans|upper`, cím, meta: `event.getDateTime()|date("M. d. H:i")` + „Tippek X/Y”, mobilon a „Tippek” rész rejtve)
- Test: `tests/Integration/Twig/ResultTableTemplatesTest.php`

**Interfaces:**
- Consumes: Task 1 osztályok; Task 3 `team_color`, `competition_ranks`, `bet_legend`.
- A sablonok változói nem változnak (`result`, `bets`, `usersCount`, `noBettingUsers`, `countableBets`, `event`). A `full`-ban: oszlopfej (1.…N. és ha van kérdés-attribútum: „SC”), „Eredmény” sor (`f1-chip--res` + `style="border-left-color: {{ team_color(value) }}"`, kérdés chip semleges szürke csíkkal), tippelő sorok: `competition_ranks(bets|map(b => b.getPointSummary()))[loop.index0]` · név · chipek (`bet-high` → `f1-chip--exact`, `bet-low` → `f1-chip--near`, egyéb → alap) · pont; jelmagyarázat `bet_legend(result.getEvent().getType())` alapján (near elem csak ha `near > 0`, SC elem csak ha `question` nem null). A „nem tippelt” névsor (`noBettingUsers`) `.f1-board__note`-ban marad.
- `only_bets`: ugyanaz a sorszerkezet eredménysor, pont és jelmagyarázat nélkül, minden chip alap stílusú, sorszám nélkül.
- `only_users`: `.f1-board__note` a tippelők névsorával vagy `result_betting_nobody` szöveggel; a rejtett `#event-table-bet-numbers` / `#event-table-event-id` inputok maradnak.

- [ ] **Step 1: Failing tesztek** (`KernelTestCase`, `static::getContainer()->get('twig')->render(...)`; a sablonváltozókat névtelen osztályokkal stubolja: `getUser()->getName()`, `getPointSummary()`, `getAttributes()` → stub `getKey/getValue/getClass`, `result.getEvent().getType()/getName()/getDateTime()`; az assert `Symfony\Component\DomCrawler\Crawler`-rel):
  - `test_full_race_marks_exact_and_near_chips`: `bet-high` → `.f1-chip--exact`, `bet-low` → `.f1-chip--near`, és egyik tippelő-chipen sincs `style` attribútum.
  - `test_full_result_row_uses_team_colors`: `VER` eredmény chip style tartalmazza `#3671C6`.
  - `test_full_ranks_ties`: pontok `[22,18,18,15]` → `.f1-pos` szövegek `1,2,2,4`; tipptáblában nincs `.f1-pos--p1`.
  - `test_full_question_chip_is_last_in_row`: race esetén minden tippsor 11 chip, az utolsó `.f1-chip--q`.
  - `test_sprint_table_has_eight_columns_and_no_near_legend`: sprint_qualify, 8 attribútum → `--cols: 8`, a `.f1-legend` nem tartalmaz `rossz helyen` szöveget.
  - `test_only_users_without_bets_shows_nobody_text`: üres `bets` → `result_betting_nobody` fordítása jelenik meg, nincs `.f1-tiprow`.
- [ ] **Step 2: Futtatás** – `... vendor/bin/phpunit tests/Integration/Twig/ResultTableTemplatesTest.php` · Expected: FAIL
- [ ] **Step 3: Sablonok átírása**
- [ ] **Step 4: Futtatás** · Expected: OK
- [ ] **Step 5: Vizuális ellenőrzés** – `npm run dev`, Playwright `/#/actual` 375 és 1366 px: a mockup v11 „Futam”/„Időmérő” paneljével egyezik.
- [ ] **Step 6: Commit** – `git commit -m "Restyle event result tables as TV-style boards"`

### Task 5: Hétvégi összesítő, Eredmények oldal, Aktuális fejléc

**Files:**
- Modify: `templates/extension/result_table/type/summary.html.twig`
- Modify: `templates/controller/module/results.html.twig` (hétvégénként `.f1-weekend` szekció: hétvége-cím sáv, alatta a táblák)
- Modify: `src/Controller/Module/ResultsController.php` (cache kulcs: `'results_v2_' . count($results)`)
- Create: `templates/extension/actual_weekend_header.html.twig`
- Modify: `src/Twig/ActualEventsRuntimeExtension.php::renderActualEvents()` (a táblák elé rendereli a fejlécet), `templates/extension/actual_events_results.html.twig`
- Test: `tests/Integration/Twig/SummaryTemplateTest.php`

**Interfaces:**
- Consumes: Task 3 `competition_ranks`.
- Összesítő oszlopai: Tippelő · „Hétvégi pont” (`getCollectedPoints`, `.f1-num--dim`) · „Bajnoki pont” (`getPoints`, `.f1-num--main`, `+N` ha N>0, különben `0`). Helyezés: `competition_ranks` a `getCollectedPoints` listán; dobogós osztály a helyezés alapján (`--p1..3`). Tag: „HÉTVÉGE”, cím: `result.0.race.name|split(' - ').0` + „Összesített eredmény”.
- Aktuális fejléc (csak ha van esemény): `{{ events|first.getWeekendOrder() }}. forduló` · nagy cím = futam (vagy első esemény) neve `|split(' - ').0|upper` · dátumtartomány (`min`–`max` `getDateTime`, formátum `M. d.`). Nincs helyszín.
- `renderActualEvents()` szignatúra változatlan; a fejléc HTML a táblák elé kerül a visszaadott stringben, így a `module_reload` újratöltéskor is megmarad.

- [ ] **Step 1: Failing tesztek**: `test_summary_ranks_ties_and_formats_points` (collected `[31,25,24,24,0]`, points `[14,10,8,8,0]` → helyezések `1,2,3,3,5`; bajnoki pont szövegek `+14,+10,+8,+8,0`; `.f1-pos--p3` két sorban); `test_summary_empty_result_renders_nothing`.
- [ ] **Step 2: Futtatás** · Expected: FAIL
- [ ] **Step 3: Implementáció** (sablonok, fejléc, cache kulcs)
- [ ] **Step 4: Futtatás** – `... vendor/bin/phpunit tests` · Expected: OK
- [ ] **Step 5: Playwright** `/#/actual` és `/#/results` 375/1366 px – sorrend: fejléc, összesítő, futam, időmérő; Eredmények oldalon több hétvége (sprinthétvége is) hibátlan.
- [ ] **Step 6: Commit** – `git commit -m "Restyle weekend summary, results page and add weekend header"`

### Task 6: Oldalsáv Twig modulok

**Files:**
- Modify: `templates/controller/module/base_module.html.twig` (→ `.f1-board` + `.f1-board__head`; `heading_title` blokk a címet adja, új `heading_tag` blokk a piros címkét, alapértelmezés üres; a `details_link` ikon a fejléc jobb szélén marad)
- Modify: `templates/extension/user_championship.html.twig`, `templates/controller/module/trophies.html.twig`, `templates/extension/prize_draw.html.twig`, `templates/extension/point_calculating_info.html.twig`, `templates/controller/module/message_wall/*.html.twig`, `templates/extension/point_summary_chart_js.html.twig`

**Interfaces:**
- Consumes: Task 1 osztályok (`.f1-trophies`, `.f1-trophy`, `.f1-stat`), Task 3 `competition_ranks` nem kell (a pontverseny sorrendje a resolverből jön, sorszám `loop.index` marad, mert a holtversenyt a kupák döntik el).
- Egyéni pontverseny sor: helyezés (`--p1..3`) · név · online pötty (`.user-activity user-{{ name }} online-dot` osztályok MARADNAK – a `messages.js` ezekre ír) · kockás zászló (feltétel változatlan) · kupák (`.f1-trophy` inline SVG `trophy_{type}.svg` path-szal, `color` tompított: gold `#cfae4f`, silver `#b4b4be`, bronze `#b98252`; csak ha van) · hátrány (`.f1-num--dim`, a mostani `+ N` formátumban, ahogy az eredeti sablon írja; a mockup „−23” formája csak illusztráció volt) · pont. Alatta: „Matek” két `.f1-stat` (hátralévő hétvége, még szerezhető pont `(NX{{ placePoint(1) }})`), rekordok listája, magyarázó szövegek `.f1-muted`.
- Tag-ek: Kupák → „KUPÁK”, Egyéni pontverseny → „VB”, Nyeremény → „NYEREMÉNY”, Pontátváltás → „PONTOK”, Grafikon → „VB”, Üzenőfal → „FAL”.
- Chart.js: `Chart.defaults.color = '#9a9aa8'`, `Chart.defaults.borderColor = 'rgba(255,255,255,.06)'`, a grafikon alatti szöveg `.f1-muted`.

- [ ] **Step 1: Sablonok átírása**
- [ ] **Step 2: Futtatás** – `... vendor/bin/phpunit tests` · Expected: OK (regresszió)
- [ ] **Step 3: Playwright** 375/1366 px: oldalsáv sorrend (Kupák, Egyéni pontverseny + matek + rekordok, Nyeremény, Pontátváltás, VB) a spec szerint; online pötty megjelenik belépett másik felhasználónál; pontszámok egyeznek a `master`-en láthatóval.
- [ ] **Step 4: Élő frissítés** – böngészőben nyitva az oldal, közben admin felületen új eredmény/tipp mentése (vagy `entity_counts` változás): 10–20 mp-en belül a blokk magától az új stílusban újratöltődik.
- [ ] **Step 5: Commit** – `git commit -m "Restyle sidebar modules, message wall and chart"`

### Task 7: Vue komponensek – VB, Hírek, Aktuális állapotok

**Files:**
- Modify: `src/Controller/Module/ResultsOfChampionshipController.php::indexAction` (válasz előtt, cache után: minden `driverStandings[i]['teamColor'] = colorForDriver(Driver.code)`, minden `constructStandings[i]['teamColor'] = colorForTeam(Constructor.name)`; `TeamColorProvider` injektálva)
- Modify: `assets/js/components/ChampionshipResult.vue`, `assets/js/components/News.vue`, `assets/js/components/Actual.vue` (hiba-panel `.f1-board` stílusban)
- Test: `tests/Integration/Controller/ChampionshipResultColorsTest.php`

**Interfaces:**
- Consumes: Task 3 `TeamColorProvider`.
- ChampionshipResult: két `.f1-board` – „F1 · Pilóta világbajnokság” (helyezés · `.f1-stripe` `teamColor` · vezetéknév + `<small>` keresztnév · wins `.f1-num--dim` · pont) és „F1 · Konstruktőri VB” (helyezés · csík · név · pont). A `PulseLoader` színe `#e10600`.
- News: `.f1-board` „HÍREK” taggel; hírenként kép (mobilon teljes szélesség, desktopon bal 120 px), dátum `.f1-muted`, cím, leírás; forrás link a fejléc meta részében.

- [ ] **Step 1: Failing teszt** – `test_standings_include_team_colors`: a FileCache-be előre írt fixture-adattal (`RESULTS_CACHE_KEY`, egy `VER` pilóta + `McLaren Racing` csapat) `GET /module/championship_result` → `driverStandings[0].teamColor === '#3671C6'`, `constructStandings[0].teamColor === '#FF8000'` (`WebTestCase`).
- [ ] **Step 2: Futtatás** · Expected: FAIL
- [ ] **Step 3: Controller + Vue komponensek**
- [ ] **Step 4: Futtatás** – `... vendor/bin/phpunit tests` · Expected: OK; `npm run dev` · Expected: compiled successfully
- [ ] **Step 5: Playwright** 375/1366 px – VB táblák csapatcsíkkal, hírek olvashatók.
- [ ] **Step 6: Commit** – `git commit -m "Restyle championship standings, news and actual page states"`

### Task 8: Takarítás és teljes ellenőrzés

**Files:**
- Modify: `assets/css/app.css`, `assets/css/bet.css` (a már nem használt, a témával ütköző szabályok törlése: `.panel-weekend*`, `.result-striped*`, `.bet-high/.bet-low/.bet-empty*`, `.user-championship-trophy*`, régi `.table-striped` színek – csak ha `grep` szerint sehol nem hivatkozott)

- [ ] **Step 1: Holt CSS törlése** – minden törölt szelektorra `grep -rn "<osztály>" templates assets/js src` üres.
- [ ] **Step 2: Teljes tesztfutás** – `... vendor/bin/phpunit tests` · Expected: OK; `npm run build` · Expected: compiled successfully
- [ ] **Step 3: Playwright körút** 360, 375, 1366 px-en, belépve és kijelentkezve, minden route-on: `scrollWidth <= innerWidth`, nincs konzolhiba, egy 20+ karakteres felhasználónév (ideiglenesen dev DB-ben) ellipszissel jelenik meg.
- [ ] **Step 4: Számegyezés** – ugyanazon a dev DB-n `master` és `design27` Aktuális + Eredmények oldalán a pontok, helyezések, kupák, matek egyeznek (képernyőkép-összevetés).
- [ ] **Step 5: Commit** – `git commit -am "Remove obsolete styles after redesign"`
