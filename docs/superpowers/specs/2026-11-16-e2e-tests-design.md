# E2E tesztek (CodeceptJS) – terv

## Cél és miért

Böngészőben, a valódi UI-on végigkattintott end-to-end tesztek az F1Tipp alkalmazásra.
Nem csak a végpontokat akarjuk ellenőrizni, hanem azt is, hogy egy felhasználó ténylegesen
végig tudja-e vinni a fő folyamatokat: belépés → tippleadás (határidőn belül) → tipp
megjelenítése → admin eredményt rögzít → pontok, tabella, trófeák helyesen számolódnak.
Ez háló a folyamatban lévő redesign (design27) és a későbbi refaktorok alá.

## Célok

- CodeceptJS + Playwright helper, headless Chromium, a hoston futtatva (Node 22).
- Teljesen külön teszt-adatbázis (`f1tipp_e2e`), a fejlesztői adatbázist a tesztek nem érintik.
- A tesztek maguk hozzák létre a felhasználókat és az eseményeket; nincs kézi előkészítés.
- Az alább felsorolt szenáriók lefedése. A lista később bővül.

## Nem célok

- CI/CD (GitHub Actions) – egyelőre csak lokális futtatás Dockerből.
- Google OAuth belépés tesztelése.
- Külső szolgáltatások (pilóta-/konstruktőri VB API, motorsport.com feed, borítókép cron).
- Vizuális regressziós (screenshot-összehasonlító) teszt.

## Anti-célok (ettől lenne kudarc, akkor is, ha „működik”)

- Ha a tesztfuttatás bármilyen módon módosítja a fejlesztői adatbázist, cache-t vagy `.env.local`-t.
- Ha a tesztek az adatbázison/API-n keresztül „csalnak” azon a lépésen, amit épp tesztelnek
  (pl. a tippet SQL-lel szúrják be a tippleadás-tesztben). Előfeltételt lehet seedelni,
  a tesztelt lépést a UI-on kell végigcsinálni.
- Pöttyös (flaky) tesztek: időzítésre `wait`-elés helyett állapotra várunk.
- Olyan seed/reset kód, ami nem `e2e` környezetben is lefuthat.

## Korlátok (a kódból)

- **Határidő:** `Betting::indexAction` a szerver órájához méri: `now < event.date_time`.
  A seed eseményidőpontjai a futás pillanatához relatívak (pl. most +2 óra, most −1 óra).
- **Aktuális hétvége:** globális – `EventRepository::getActualWeekendEvents()` a legközelebbi
  jövőbeli esemény `weekendOrder`-ét veszi, Doctrine result cache-sel
  (`AppEntity…ActualWeekendEvents`, `…NextEvent`). Reset után ezeket a cache-eket üríteni kell.
- **Eredmény rögzítése:** csak EasyAdminban (`/admin`, ROLE_ADMIN). Új eredmény mindig a
  `getNextEventForResult()` eseményre készül (a legutóbb eredményezett utáni esemény időrendben),
  tehát a tesztek az eredményeket időrendben rögzítik.
- **Számítás:** az `EasyAdminSubscriber` mentéskor futtatja a `Calculator`-t
  (sorrend: BetResult → Trophy → UserPointSummary → Alternative → AlternativePointSummary);
  szerkesztéskor törli a hétvége pontjait/trófeáit és újraszámol.
- **Pontszabályok** (`src/Rule/RuleType`): futam p1–p10 pontos 3 / benne van 1, `sc` kérdés 3;
  időmérő p1–p10 4 / 1; sprint-időmérő p1–p8 1 / 0. Trófea (arany/ezüst/bronz) a hétvégi
  összpontszám alapján, csak `isAlterChamps = true` felhasználóknak.
- **Oldalsáv:** a tabella és a trófeák Twigből renderelődnek (`userChampionshipTableExtension`,
  `trophyExtension`), cache-sel (`userChampionshipTrophyAwareOrder`, `FileCache`).
  Az oldalsáv „VB” tabellája **nem a tippek pontját** mutatja, hanem az alternatív bajnokság
  pontjait: `alternativePointSummary` / `alternativePointDifference`, amit az `AlternativeCalculator`
  a `PointProvider` helyezési pontjaiból (jelenleg 14/10/8/7/6/4/3/2/1/0 a hétvégi helyezés szerint)
  számol; a sorrend `UserChampionshipOrderResolver` szerint (alt. pont, majd arany/ezüst/bronz).
  A tippek nyers pontja (`Bet.pointSummary`) az Aktuális oldalon és az eredménytáblákban látszik.
- **TrophyCalculator** egy fantom 0 pontos bejegyzéssel indul (`$userPoints = array(0)`): két
  felhasználónál a bronz a 0-s „userhez” megy, és 0 pontos user holtversenybe kerül vele.
- **Pilóta-/konstruktőri VB doboz:** minden oldal betölti (`/module/championship_result`), üres
  `FileCache` esetén rapidapi-t hív `RAPIDAPI_KEY`-jel, ami lokálisan nincs beállítva.
- **FileCache** jelenleg fix útvonalat használ: `var/cache/file-cache` – minden környezetben
  ugyanazt. Az e2e környezet saját mappát kap, különben az e2e futás a dev cache-t írná/törölné.
- A `docker-compose.override.yml` a `.git/info/exclude`-ban van (csak ennek a gépnek a minikube
  miatti portja), a `docker-compose.yml`-ben pedig vannak nem commitolandó lokális módosítások.
- **Login:** `json_login` a `/login` útvonalon (`username`, `password`), remember-me mindig be.
- **Migrációk:** a `src/Migrations` üres, a séma `doctrine:schema:create`-tel áll elő.
- A dev app a 8000-es porton fut (`docker-compose.override.yml`, a 80-ast a minikube foglalja).

## Megközelítések

### 1. Hogyan fut az app a teszt-adatbázison

- **A) Második nginx server blokk `APP_ENV=e2e`-vel, ugyanazon a PHP-konténeren — VÁLASZTOTT.**
  Az nginx kap egy `listen 8001` blokkot, ami `fastcgi_param APP_ENV e2e;`-vel adja át a kérést
  (a valódi env változó felülírja a `.env.local`-t). `.env.e2e` → `DATABASE_URL=…/f1tipp_e2e`.
  A dev app a 8000-en érintetlen, a cache-ek külön mappában (`var/cache/e2e`).
- B) Külön php+nginx konténerpár – több konfiguráció, nem ad többet.
- C) `.env.local` átírása futás előtt – elrontaná a dev környezetet futás közben. Elvetve.

### 2. Hogyan áll elő a tesztadat

- **A) Symfony konzolparancsok, a teszt-helperből `docker compose exec`-kel hívva — VÁLASZTOTT.**
  Csak `e2e` környezetben futnak (más env-ben hibával kilépnek).
- B) Csak e2e-ben élő HTTP seed-végpont – gyorsabb, de veszélyes kódot tesz a web útvonalak közé.
- C) Közvetlen SQL Node-ból – megkerüli a Doctrine-t és a kalkulátort. Elvetve.

### 3. Teszt stílus

Sima CodeceptJS JS szenáriók page objectekkel, nem Gherkin (a builder döntése, a user nem kérte
a Gherkint; ennél a méretnél felesleges réteg).

## A döntött „hogyan”

### Környezet

- `.docker/nginx/nginx.conf`: második server blokk (konténeren belül pl. `listen 81`), `APP_ENV=e2e`.
- Port: a commitolt `docker-compose.yml` `web` szolgáltatása kap egy `8001:81` mappinget (csak ez a
  hunk kerül stage-elésre, a lokális DB-név módosítás nem); a helyi, nem commitolt
  `docker-compose.override.yml` `!override` listájába is bekerül. Az e2e alap-URL a tesztben
  `E2E_BASE_URL` env változóval felülírható, alapértéke `http://127.0.0.1:8001`. *(builder döntése)*
- `.env.e2e` (commitolva, nincs benne titok): `APP_ENV=e2e`, `APP_DEBUG=1`,
  `DATABASE_URL=mysql://root:root@mysql_db:3306/f1tipp_e2e`, dummy Google OAuth értékek.
- `config/packages/e2e/`: a `dev` doctrine/cache konfiguráció megfelelője (a `when@` szintaxis
  5.4-ben nincs, ezért mappa).
- `FileCache` útvonala paraméterből jön (pl. `app.file_cache_dir`), alapértéke a mostani
  `var/cache/file-cache` (dev/prod nem változik), `e2e`-ben `%kernel.cache_dir%/file-cache`.
  *(builder döntése: így a dev cache nem vész el)*
- Pilóta-/konstruktőri VB: az `app:e2e:reset` fix, kitalált állást ír az e2e `FileCache`-be
  (ugyanabban a formában, ahogy a rapidapi-válasz tárolódik), így nincs hálózati hívás, és a doboz
  determinisztikus. A Playwrightban ezen felül minden nem `127.0.0.1`-re menő kérés blokkolva
  (Google fontok/ikon, gtag), hogy a tesztek ne függjenek a hálózattól. *(builder döntése)*
- Assetek: az e2e app ugyanazt a `public/build`-et használja; futás előtt `npm run dev`
  (vagy a futó `watch`) kell. A futtató szkript ezt ellenőrzi / dokumentálja.

### Seed parancsok (`src/Command/E2e/…`, csak `e2e` env)

- `app:e2e:reset` – adatbázis létrehozása, ha nincs; séma eldobása és újraépítése;
  alapadatok: pilóták (néhány valós kóddal, pl. VER, NOR, LEC, HAM, PIA, RUS, SAI, ALO, …
  legalább 12 db, hogy a p1–p10 kitölthető legyen), felhasználók; minden cache ürítése
  (Doctrine result/second-level cache, `FileCache`).
  - `e2e_user`, `e2e_user2`, `e2e_user3` – ROLE_USER, `isAlterChamps = true` (három kell, hogy
    a dobogó arany/ezüst/bronz valódi userekkel teljen ki) *(builder döntése)*
  - `e2e_admin` – ROLE_ADMIN, `isAlterChamps = false`
  - a jelszavak fixek, a tesztkódban konstansként; a seed fix (előre generált) jelszó-hash-t ír,
    hogy a mentett session reset után is érvényes maradjon.
- `app:e2e:scenario <név>` – reset után szenárióállapot építése, időpontok a futás idejéhez
  relatívan. Kezdő készlet (a builder bővítheti):
  - `open-weekend` – egy hétvége: időmérő (most +1 óra), futam (most +1 nap), opcionálisan sprint;
  - `deadline-passed` – időmérő lejárt (most −1 óra), futam még nyitott;
  - `about-to-close` – időmérő most +N másodperc (a „kitöltés közben lejár” esethez);
  - `bets-placed` – `open-weekend` + mindkét usernek leadott tippje (a pontszámítás-tesztekhez),
    és a határidők már lejártak, hogy az admin eredményt rögzíthessen.
  - Előző hétvégék eredménnyel a tabella-pontkülönbséghez, ha kell.
- Seed által létrehozott tippek/eredmények a valódi entitásokon és a `Calculator`-on mennek át,
  nem kézi pontbeírással.

### Teszt projekt (`tests/e2e/`)

- Saját `package.json` (vagy a gyökérben devDependency – a builderé): `codeceptjs`, `playwright`.
- `codecept.conf.js`: `url: http://127.0.0.1:8001`, Playwright helper, Chromium, headless
  alapból, `show: true` env változóval kapcsolható.
- Saját helper (`E2eHelper`): `resetDb()`, `seedScenario(name)` →
  `docker compose exec -T php php bin/console … --env=e2e`.
- Page objectek: Login, Navigation, Betting (fülek, DriverSelect, SC kérdés, beküldés),
  Actual, Results, Admin (eredmény rögzítése / szerkesztése), Sidebar (tabella, trófeák).
- Login szenárión kívül a belépés session-mentéssel/újrahasznosítással (Playwright storage state
  vagy CodeceptJS `autoLogin`) gyorsítva.
- `npm run e2e` (és `e2e:headed`) parancs; README/CLAUDE.md-ben pár sor a futtatásról.
- Minden szenárió önálló: `Before`-ben reset + szenárió seed, nincs sorrendfüggés.

### Szenáriók (első kör)

1. **Belépés**
   - jó jelszóval belép, a navigációban megjelenik a név;
   - rossz jelszó → hibaüzenet, nincs belépve;
   - kilépés;
   - vendégként a Tippelek oldal nem ad formot (belépésre utal).
2. **Tippleadás**
   - a hétvége eseményei fülként jelennek meg (időmérő / sprint / futam);
   - amíg nincs minden pozíció kitöltve, a beküldés gomb tiltott, a hiányzó darabszám látszik;
   - ugyanaz a pilóta nem választható két pozícióra (DriverSelect jelzi);
   - futamnál a safety car kérdés megválaszolható;
   - sikeres mentés → siker üzenet, átirányítás az Aktuális oldalra; a „Maradok” gomb megállítja.
3. **Határidő**
   - lejárt eseménynél a form zárolt, nincs beküldés gomb;
   - kitöltés közben lejár a határidő → beküldéskor `betting_time_out` hiba, a tipp nem mentődik.
4. **Tipp megjelenítése**
   - mentés után a fül újra megnyitva zárolt formot mutat a mentett értékekkel;
   - a tipp megjelenik az Aktuális oldalon / az eredménytáblákban.
5. **Eredmény és pontszámítás** (három userrel, ismert, egymástól eltérő pontot hozó tippekkel)
   - admin rögzíti az időmérő eredményét → a tipp pontjai a szabály szerint (pontos 4, benne 1),
     az Aktuális oldalon / eredménytáblában ellenőrizve;
   - futam eredmény + SC → 3 / 1 / SC 3;
   - a pontos/közeli jelölés (lila/sárga marker) a megfelelő mezőkön;
   - oldalsáv „VB” tabella a hétvége lezárása (futam eredmény) után: sorrend, alternatív pont
     (helyezési pont a hétvégi összpontszám szerint), pontkülönbség a vezetőtől;
   - trófeák (arany/ezüst/bronz) a hétvégi pontok szerint, az oldalsávban és a Trófeák oldalon.
6. **Eredmény javítása**
   - admin módosít egy eredményt → a tipp pontjai, a tabella és a trófeák újraszámolódnak.
7. **Egyéb oldalak (füstteszt)**
   - üzenőfal: belépett user üzenetet ír, megjelenik;
   - Statisztika, Naptár, Szabályok, Trófeák oldal betölt; a böngészőkonzolban nincs hiba, és nincs
     5xx válasz az app saját kéréseire. *(builder döntése)*

A konkrét várt pontszámokat a builder a szabályokból számolja ki, és a tesztben konstansként
rögzíti (a tesztnek nem szabad a kalkulátor kódját „visszaszámolnia”). A szabálytáblák
(tipp pontok és `PointProvider` helyezési pontok) egyetlen teszt-konstans fájlba kerülnek, mert a
még nem mergelt `2026_rules` branch a helyezési pontokat 15/11/9/7/6/5/4/2/1/0-ra változtatja –
ha az bekerül, ott kell frissíteni. A tesztek a `design27` jelenlegi szabályait követik.

### Talált alkalmazáshibák

A tesztek által felfedett valódi hibákat ebben a branchben nem javítjuk: a teszt piros marad
(vagy skip megjegyzéssel), és a hibát jelezni kell a usernek. *(builder döntése)* Az előzetes
átnézés alapján várhatóan ilyen:
- `TrophyCalculator` fantom 0 pontos bejegyzése (0 pontos user holtversenye / null userű trófea);
- a `topWeekendUsers` cache eredmény szerkesztésekor nem ürül.

## Ami a builderre marad

- A pontos fájl- és mappaszerkezet, helper- és page object API.
- Port leképezés részletei, a seed parancsok belső felépítése.
- Szelektorok: ahol nincs stabil szelektor, `data-test` attribútumot lehet tenni a Vue/Twig
  sablonokba (ez az egyetlen engedett termékkód-változtatás a FileCache és a konfiguráció mellett).
- Ha egy szenárió valódi hibát talál az appban: a teszt maradjon piros/skip-elt megjegyzéssel,
  a hibát jelezni kell, nem csendben megkerülni.

## Munkamenet

- Branch: `e2e-tests`, a `design27`-ből elágaztatva. Minden ide kerül.
- A munkafa nem commitolt dev-módosításai (`.env`, `docker-compose.yml`, untracked fájlok)
  nem kerülnek a commitokba.
