# Bajnoki pontozási tábla adatbázisból – terv

Branch: `extract-static-points`

## Miért

A hétvégi helyezés → bajnoki pont tábla (15, 11, 9, 7, 6, 5, 4, 2, 1, 0) be van égetve a
`src/Calculator/Provider/PointProvider.php`-ba. Ha évente változik, deployolni kell, és a teszteket is igazítani.
Cél: a táblát az adminban lehessen szerkeszteni, adatbázisból jöjjön, cache-elve, és a tesztek ne a kódba égetett
értékre támaszkodjanak.

## Célok / nem célok

- Cél: Doctrine-entitás + 2LC cache, EasyAdmin CRUD, a számolás és a megjelenítés (pontinfó-modul, „hátralévő
  pontok”) ebből dolgozik.
- Cél: mentéskor a már kiszámolt hétvégék automatikusan az új tábla szerint újrapontozódnak (A megoldás).
- Cél: e2e seed a mostani értékekkel (a meglévő feature-ök változatlanok), plusz új forgatókönyvek módosított táblára.
- Cél: éles SQL (CREATE TABLE + INSERT a mostani értékekkel).
- Nem cél: a kupapontok (25/18/15, `SeasonTrophiesController`) és a tippenkénti pontok (`src/Rule`) kiemelése.
- Nem cél: verziózott / érvényességi dátumos tábla (a DB-ben mindig csak az aktuális szezon van).
- Anti-cél: a megjelenített tábla és a tárolt bajnoki pontok ne mondhassanak ellent egymásnak.

## Megközelítések

- **A) Egy tábla, mentéskor automatikus újrapontozás – választott.** A hétvégi összpont (`collected_points`) el van
  tárolva, így csak a helyezés → pont átváltás és a bajnoki összegzés fut újra; olcsó, mindig konzisztens.
- B) Egy tábla, zárolva, ha van kiszámolt hétvége – szezon közbeni elírás nem javítható.
- C) Verziózott tábla érvényességi dátummal – egy szezonos DB-hez túl sok kód és UI, két pontrendszert kellene
  magyarázni.

## Terv

**Entitás** `App\Entity\ChampionshipPoint` (tábla `championship_point`): `id`, `place` (int, egyedi), `points` (int,
≥ 0). Soronként egy helyezés, így a helyezések száma is szerkeszthető (P11 felvehető, P10 törölhető).
`#[ORM\Cache(NONSTRICT_READ_WRITE)]`, a lekérdezés cacheable (2LC query cache), mint a többi entitásnál.

**Provider** `PointProvider` megmarad a mostani API-val (`getPlacePoint($place)`, `getPlacePoints()`), de a
repositoryból tölt (konstansok törlődnek). A Twig-extensionök `new PointProvider()` helyett DI-ből kapják. A
táblában nem szereplő helyezés 0 pont (mint eddig).

**Átváltás tiszta függvényként:** a holtverseny-kezelés (`AlternativeCalculator::calculateUsersAlternativePoints`)
kiemelve egy tiszta, DB-független osztályba (gyűjtött pontok rendezett listája + pontozási tábla → bajnoki pontok),
a jelenlegi szabályokkal: azonos pontszám = azonos bajnoki pont, a következő helyezés kihagyja a holtversenyt,
0 gyűjtött pont = 0 bajnoki pont. Ezt használja a számolás és az újrapontozás is.

**Újrapontozás mentéskor:** `ChampionshipPointCrudController` felülírja a persist/update/delete-et: mentés után egy
`ChampionshipPointRecalculator` szolgáltatás minden kiszámolt hétvégére újraalkalmazza a táblát a tárolt
`collected_points`-ra, majd lefuttatja az `AlternativePointSummaryCalculator`-t (összeg, különbség, oldalsáv-sorrend
cache törlés). Flash üzenet: „N kiszámolt hétvége újrapontozva”. A szerkesztő oldalon figyelmeztetés, hány hétvégét
érint a mentés. Validáció: helyezés ≥ 1 és egyedi, pont ≥ 0.

**Admin:** új menüpont („Bajnoki pontok”), ROLE_SUPER_ADMIN, magyar fordítások a `messages.hu.xlf`-ben.

**Tesztek:**
- PHPUnit unit teszt a tiszta átváltásra: mostani tábla, egyedi tábla (pl. 25/18/15), holtversenyek, 0 pont,
  több résztvevő, mint helyezés a táblában.
- E2E: `E2eFixtures` betölti a mostani 10 sort, így a meglévő feature-ök változatlanok. Új `point_table.feature`:
  admin átírja a táblát lezárt hétvége után → az eredmények és a bajnoki tabella az új szerint; tábla módosítása
  eredmény felvitele előtt → a számolás már az újat használja; holtverseny új táblával; a pontinfó-modul és a
  „hátralévő pontok” (P1 × hátralévő hétvégék) az új értéket mutatja; helyezés törlése → az a hely 0 pont.

**Éles DB:** SQL a végén (CREATE TABLE InnoDB utf8mb4 + 10 INSERT), lokálisan is ezzel alkalmazva, majd
`doctrine:schema:update --dump-sql`-lel ellenőrizve, hogy nincs eltérés. Feltöltés után prod cache törlés.

## A builderre hagyva

Fájl- és osztálynevek részletei, a figyelmeztetés pontos szövege, az e2e lépések megfogalmazása.
