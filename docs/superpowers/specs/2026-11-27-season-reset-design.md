# Szezonváltás admin oldal (nullázás + élő checklist) – design

Dátum: 2026-11-27 · Státusz: tervezet

## Cél és miért

Minden szezon végén ma kézzel, SQL-lel kell törölni a tippeket, eredményeket, kupákat, alternatív pontokat és a felhasználók pontjait. A szezonváltás teendőit egy Obsidian checklist tartja nyilván. A cél egy admin menüpont:

1. **Szezon nullázása:** egy gombnyomással törli a szezon játékadatait. Előtte pontosan kiírja, mit töröl (darabszámokkal), és csak egy véletlenszerű kód beírása után fut le. Így véletlen nullázás nem fordulhat elő.
2. **Élő checklist:** ugyanazon az oldalon a szezonváltás teljes checklistje. Ahol lehet, automatikus ✓/✗ állapottal, a többi pont szöveges emlékeztető.

## Célok, nem célok, anti-célok

**Célok**
- Kézi DB-munka nélkül nullázható a szezon, az éles (Nethely, csak FTP) környezetben is.
- Lehetetlen véletlenül nullázni (beírandó kód, szerveroldali ellenőrzés).
- Lehetetlen úgy nullázni, hogy a lezárt szezon végeredménye elvész (archív-ellenőrzés).
- A checklist egy helyen, a valós állapottal együtt látszik.

**Nem célok**
- Az események (naptár) módosítása: **az események érintetlenek maradnak** (user döntése). A naptárat a user kézzel írja át.
- Az archív Információ-bejegyzés automatikus generálása (külön ötlet, lásd a végén).
- Beépített DB-mentés. A mentés kézi lépés a checklistben (Nethely phpMyAdmin export).
- Visszavonás (undo).

**Anti-célok (hiba, még ha technikailag működik is)**
- Bármilyen adat törlődik a kód beírása nélkül, vagy rossz kóddal.
- Nullázás úgy, hogy a lezárandó szezon archív bejegyzése hiányzik.
- Részleges nullázás (pl. a tippek törlődtek, a pontok nem).
- A nullázás után a cache-ből még a régi pontok látszanak.
- A tesztek a fejlesztői `f1tippelek` adatbázist nullázzák.

## Megközelítések

1. **A checklist a kódban (Twig template), az élő ellenőrzések egy PHP service-ben.** ← **Választott.** Egy lista, egy sorrend. A szöveges pontok mellett ott az élő állapot és a link a megfelelő admin menüre. Hátránya: a szövegváltozás deployt igényel. A checklist évente egyszer változik, és az ilyen változás jellemzően amúgy is kódmódosítással jár.
2. **A checklist szövege az adatbázisban (Információ-bejegyzés), az élő ellenőrzések külön blokkban.** Elvetve: két lista lenne, amelyek elcsúsznak egymástól, és a szövegbe nem kerülhet állapot.
3. **Konzolparancs a nullázásra.** Elvetve: élesen nincs shell, csak FTP.

## A nullázás viselkedése

### Mit töröl / módosít

Minden egy DBAL-tranzakcióban fut, `DELETE`-tel (**nem `TRUNCATE`**: a MySQL-ben a `TRUNCATE` implicit commitot csinál, ezzel megtörné a tranzakciót). Sorrend az idegen kulcsok miatt (`result.event` és `alternative_championship.*` RESTRICT):

| Tábla | Művelet |
|---|---|
| `bet_attribute` | `DELETE` (minden sor) |
| `bet` | `DELETE` |
| `result_attribute` | `DELETE` |
| `result` | `DELETE` |
| `alternative_championship` | `DELETE` |
| `trophy` | `DELETE` |
| `user` | `point_summary = 0`, `point_difference = NULL`, `alternative_point_summary = NULL`, `alternative_point_difference = NULL` |

- A `user.point_summary` a DB-ben `NOT NULL`, ezért 0-t kap, nem NULL-t. A régi SQL (és a mostani „Pontok újraszámolása”) NULL-t ír a `point_summary`-be. Ez csak a MySQL 5.6 nem-strict módja miatt nem hibázik. Ezt a hibát nem javítjuk ebben a körben, csak jelezzük.

**Megmarad (nem nyúl hozzá):** `event`, `user` (sorok, szerepkörök, `is_alter_champs` jelölés), `driver` (a `point` is!), `championship_point`, `prize_draw`, `prize`, `information`, `setting`, `message_wall`, `feed`.

### Nullázás után

- `EntityManager::clear()`, majd a second-level cache teljes ürítése: entity-, collection- és query-régiók. A `User`, `Trophy`, `Result`, `AlternativeChampionship`, `Driver` 2LC-ben van.
- `FileCache::clearAll()`.
- A Doctrine result cache (`src/Cache/ResultCache.php`, `cache.app` pool) teljes ürítése: `ResultCache::deleteAll()`. Ebben van például a `userChampionshipTrophyAwareOrder` kulcs, ezért enélkül a régi sorrend maradna látható.
- Flash üzenet a ténylegesen törölt darabszámokkal, majd visszairányítás ugyanerre az oldalra.

### Lezárandó szezon éve és az archív-ellenőrzés (tiltó)

- **Lezárandó szezon éve (`closingYear`)** = `YEAR(MAX(event.date_time))` azok közül az események közül, amelyekhez van `result` sor.
  - Nem a mai dátumból számoljuk, mert a nullázás januárban is történhet.
- Ha van ilyen év: a nullázás **csak akkor engedélyezett**, ha létezik `slug = closingYear` Információ-bejegyzés. Ha nincs, piros figyelmeztetés jelenik meg egy linkkel az Információk menüre, a gomb és a kódmező pedig inaktív.
- Ha nincs egyetlen eredmény sem (nincs lezárandó szezon): nincs archív-feltétel. Az oldal kiírja, hogy „Nincs lezárandó eredmény”. A nullázás ilyenkor is engedélyezett, de csak ha van mit törölni. Ha minden darabszám 0, a gomb inaktív („Nincs mit nullázni”).
- **Évszám-tiltás (a user döntése, 2026-11-27):** a nullázás csak akkor engedélyezett, ha a mai év nagyobb, mint a `closingYear`. Egy 2026-os szezont tehát legkorábban 2027. január 1-jén lehet nullázni.
  - Ok: az Eredmények oldal évfüleit a böngésző órája adja (`Results.vue`), az aktuális év füle az élő adatot mutatja. Egy decemberi nullázás után január 1-ig üres lenne az idei fül.
  - A user amúgy is a szezonkezdet (március) előtt pár héttel nullázik.
  - Elvetve: (a) az idei fül is az archívumot mutassa, ha az létezik – ez `Results.vue`-módosítást igényelne; (b) csak figyelmeztetés.
  - Üzenet: „A 2026-os szezon 2027. január 1-jétől nullázható.”
- **Második tiltó feltétel (Claude döntése):** ha van olyan `result`, amelynek az eseménye jövőbeli dátumú, a nullázás tiltva. Üzenet: „Az eseményeket a nullázás után írd át.”
  - Ez a rossz sorrend ellen véd: ha a naptárt a nullázás előtt írnák át, a `closingYear` elcsúszna, és az archív-ellenőrzés rossz évet keresne, vagy átengedné a nullázást.
  - Ha a jövőbeli eseményekre már van tipp, az oldal figyelmeztet, hogy ezek is törlődnek. Ez csak figyelmeztetés, nem tilt.
- A feltételeket a **POST-nál a szerver újra ellenőrzi**, nem csak a UI tiltja.

### Megerősítő kód

- 6 jegyű szám (`random_int(0, 999999)`, 6 jegyre nullákkal kiegészítve). Minden GET-nél újat generálunk, és a sessionbe tesszük.
- Az oldalon jól olvashatóan jelenik meg. A felhasználónak be kell írnia egy mezőbe.
- POST: CSRF-token + kód. Az összehasonlítás `hash_equals`-szal történik. A sessionbeli kódot **minden POST után töröljük**, akár jó volt, akár rossz, így egy kód csak egyszer használható.
- Rossz kód, hiányzó session-kód vagy rossz CSRF: semmi nem törlődik. Hibaüzenet jelenik meg, és az oldal új kóddal újratöltődik.

### Hozzáférés

- `ROLE_SUPER_ADMIN`, ahogy a többi veszélyes admin funkció (Bajnoki pontok, Nyeremény). Az `/admin` egésze `ROLE_ADMIN`; ezt az oldalt a controller attribútuma szűkíti.
- Menü: Karbantartás almenü, „Szezonváltás”. A meglévő `admin_maintenance_reset_season` fordítási kulcs („Évad Reset”) újrahasznosítható vagy átnevezhető.

## Az oldal felépítése

Az EasyAdmin layoutját örökli (`@EasyAdmin/page/content.html.twig`), így a menü és a fejléc a megszokott. Az oldal sorrendje az év menetét követi:

```
Szezonváltás
─────────────────────────────────────────────
1. Szezon végén (nullázás előtt)
   ☐ DB-mentés: Nethely phpMyAdmin → export            (kézi)
   ✗ „2026” archív Információ-bejegyzés                [Információk →]
   ✓ Az archív bejegyzésekben nincs hash-es kép-URL
   ℹ „alter-2026” bejegyzés: nincs (csak ha volt alternatív összesítő)

2. Szezon nullázása – 2026
   Ezek törlődnek: 477 tipp, 46 eredmény, 83 kupa, 240 alternatív pont-sor,
   31 user pontjai
   Megmarad: események, felhasználók, „bajnokság tagja” jelölés, pilóták,
   bajnoki pontok, nyeremény, információk, beállítások, üzenőfal
   Kód: 4 8 1 9 0 3   [ ______ ]  [Szezon nullázása]
   (tiltva, ha: hiányzik az archív / még a lezárandó szezon évében járunk /
    eredmény tartozik jövőbeli eseményhez)

3. Új szezon előtt
   ✗ 50 esemény régi dátummal, eredmény nélkül          [Események →]
   ℹ Bajnoki pontok: 15-11-9-7-6-5-4-2-1-0              [Bajnoki pontok →]
   ℹ Nyeremény: nevezés nyitva / lezárva, N díjsor       [Nyeremény →]
   ℹ Résztvevők: 12 „bajnokság tagja” user              [Felhasználók →]
   ✗ Aktív pilóta csapat nélkül a f1tipp_teams.yaml-ben: XYZ  (deploy)
   ✓ Szabályzat (Információ #23) címe tartalmazza: 2026  [Szabályzat →]
   ☐ Kódban, deploy: csapatszínek/párosítás, tippenkénti pontok,
     kupapontok, download_drivers.py                    (kézi)
   ☐ Élesítés után: pending.sql, var/cache/prod, Cache ürítése  (kézi)

4. Az első futam után
   ✓ RAPIDAPI_KEY be van állítva (az érvényességét nem ellenőrzi)
   ☐ Pilótapontok szinkronja (az első futam után)       [Pilóták →]  (kézi)
   ☐ Az oldalsáv pilóta-/konstruktőr-VB már az idei állást mutatja  (kézi)
```

Jelölések: ✓/✗ élő ellenőrzés; ℹ élő információ ítélet nélkül; ☐ kézi emlékeztető (nem pipálható, nem tárolunk állapotot).

### Élő ellenőrzések pontos szabályai

| Pont | Szabály | Típus |
|---|---|---|
| Archív bejegyzés | `information.slug = closingYear` létezik. Ha nincs `closingYear`: „nincs lezárandó szezon” (semleges) | ✓/✗, **tiltja a nullázást** |
| Hash-es kép-URL | Az `closingYear` és az `alter-{closingYear}` bejegyzés tartalmában nincs `/build/images/…<8 hex>.<kiterjesztés>` minta | ✓/✗, csak figyelmeztet |
| alter-bejegyzés | `alter-{closingYear}` létezik-e | ℹ |
| Régi események | `event.date_time < most` és nincs hozzá `result` sor; darabszám. 0 → ✓ | ✓/✗ |
| Bajnoki pontok | a `championship_point` sorok helyezés szerint, kötőjellel összefűzve | ℹ |
| Nyeremény | `PrizeDraw::ID` sor `entryOpen` értéke és a díjsorok száma; ha nincs sor: „hiányzik” | ℹ |
| Résztvevők | `COUNT(user WHERE is_alter_champs = 1)` | ℹ |
| Pilóta–csapat | aktív (`status = 1`) pilóták, akiknek a `short` kódja egyik csapat `drivers` listájában sincs a `f1tipp.teams` paraméterben | ✓/✗ (felsorolja a hiányzókat) |
| Szabályzat | az Információ #23 `title` tartalmazza a `currentYear`-t, ahol `currentYear = YEAR(MAX(event.date_time))` | ✓/✗ |
| RAPIDAPI_KEY | nem üres env | ✓/✗ |

A darabszámokat a nullázási blokk is ugyanabból a service-ből kapja, mint a checklist.

## Felépítés (kód)

- `src/Controller/Admin/SeasonResetController.php`: GET (oldal + új kód) és POST (ellenőrzés + nullázás). `#[IsGranted('ROLE_SUPER_ADMIN')]`.
- `src/Season/SeasonReset.php` (service): a darabszámok lekérdezése és a tranzakciós nullázás, utána cache-ürítés. A visszatérési értéke a törölt darabszámok.
- `src/Season/SeasonChecklist.php` (service): `closingYear`, `currentYear`, az élő ellenőrzések eredményei egyszerű tömbként/DTO-ként a template-nek.
- `templates/admin/season_reset.html.twig`: a checklist szövege és az állapotok.
- `DashboardController::configureMenuItems()`: új menüpont a Karbantartás almenüben.
- `translations/messages.hu.xlf`: a menü- és flash-kulcsok.
- Sémaváltozás nincs, ezért `docs/sql/pending.sql` sem kell.

## Tesztelés

**Elv (user, 2026-11-27):** minden forgatókönyvre legyen e2e teszt, prodban ne érjen meglepetés. A unit teszt csak kiegészítés, és csak ott, ahol az e2e nem tudja előidézni a helyzetet.

### Keretek (a meglévő suite-ból)

- CodeceptJS + Playwright, magyar Gherkin (`tests/e2e/features/*.feature`). Futtatás: `npm run e2e`. Az app az e2e környezetben a `:8001`-es porton fut, saját `f1tipp_e2e` DB-vel.
- Minden forgatókönyv elején a `seedScenario()` nulláról seedel. A nullázás e2e-ben ezért biztonságos, a többi feature-t nem rontja el.
- Userek: `e2e_user`…`e2e_user3` (ROLE_USER, bajnokság tagja), `e2e_admin` (ROLE_ADMIN), `e2e_superadmin` (ROLE_SUPER_ADMIN).
- A phpunit test környezetnek **nincs saját DB-je** (a `.env.test` nem ad `DATABASE_URL`-t, nincs `dbname_suffix`), tehát a fejlesztői `f1tippelek` DB-n fut. **A nullázást phpunit-tal tilos valódi DB-n futtatni.**

### Új e2e infrastruktúra

- **Új szenáriók** az `E2eScenarios`-ban, **fix naptári dátumokkal**, nem a „most”-hoz képest, hogy januárban se legyen pöttyös:
  - `previous-season`: a tavalyi év (pl. június–július) két hétvégéje eredményekkel, tippekkel, kupákkal, alternatív pontokkal. Plusz a `{tavaly}` archív Információ-bejegyzés és egy jövőbeli idei esemény.
  - `previous-season-no-archive`: ugyanez archív bejegyzés nélkül.
  - `previous-season-moved`: ugyanez, de egy eredményes esemény dátuma már jövőbeli (a naptárt a nullázás előtt írták át).
  - `current-season`: eredmény idei, múltbeli eseményen. Ez az évszám-tiltáshoz kell. A dátum `most − 1 perc`, de legalább január 1. 00:00:00: ismert korlát, hogy január 1. első percében a forgatókönyv nem futtatható.
  - `bets-only`: tippek vannak, eredmény nincs.
  - `empty`: se tipp, se eredmény.
- **E2e-only konzolparancs** (`app:e2e:counts`, a meglévő `app:e2e:*` mintájára csak `e2e` env-ben fut): a táblák darabszámai és a userek/pilóták pontjai JSON-ban. Ezzel a „semmi nem törlődött” és a „pontosan ez törlődött, a többi megmaradt” állítások pontosan ellenőrizhetők. A UI-ellenőrzés mellett fut, nem helyette.
- Új `pages/SeasonResetPage.js` és lépések az `admin_steps.js`-ben (vagy egy új `season_reset_steps.js`-ben, a `codecept.conf.js`-be felvéve).

### E2e forgatókönyvek (`features/season_reset.feature`, `@season-reset`)

**Hozzáférés**
1. Vendég az oldal URL-jén → a belépés oldalra kerül.
2. `e2e_admin` (ROLE_ADMIN) nem látja a menüpontot, az URL-en 403-at kap, és a közvetlen POST is 403.
3. `e2e_superadmin` látja a menüpontot a Karbantartás alatt, és megnyílik az oldal.

**Megjelenítés (`previous-season`)**
4. A „törlődik” lista darabszámai pontosan a seed szerintiek: tippek, eredmények, kupák, alternatív pont-sorok, userek. A „megmarad” lista is látszik.
5. Az élő checklist minden pontja mindkét állapotában, külön forgatókönyvekben vagy egy Forgatókönyv vázlattal:
   - archív bejegyzés ✓ / ✗;
   - hash-es kép-URL ✓ / ✗ (archív bejegyzés hash-es képpel seedelve);
   - `alter-{év}` létezik / nincs;
   - régi dátumú, eredmény nélküli események: ✗ a darabszámmal / ✓, miután az adminban jövőbe tették őket;
   - bajnoki ponttábla: „15-11-9-7-6-5-4-2-1-0”, és változik, ha az adminban átírják;
   - nyeremény: nyitva / lezárva, díjsorok száma;
   - résztvevők száma: változik, ha egy usert kivesznek a bajnokságból;
   - pilóta csapat nélkül: ✗, ha az adminban felvesznek egy, a `f1tipp_teams.yaml`-ben nem szereplő kódú aktív pilótát, a kód felsorolva / ✓ alapból;
   - szabályzat címe ✓ / ✗, miután az adminban átírják;
   - `RAPIDAPI_KEY` ✓ (az e2e env-ben dummy van beállítva).
6. A checklist linkjei a megfelelő admin menüre visznek: Információk, Események, Bajnoki pontok, Nyeremény, Felhasználók, Pilóták, Szabályzat szerkesztése.

**Tiltások.** Mindegyiknél: a gomb és a kódmező inaktív, látszik az üzenet, a **közvetlen POST is elutasítva** (érvényes kóddal és CSRF-fel is), az `app:e2e:counts` változatlan.
7. Hiányzó archív (`previous-season-no-archive`) → tiltva. Ha a szuperadmin az adminban felveszi az archív bejegyzést, az oldal újratöltése után már engedélyezett.
8. A lezárandó szezon évében járunk (`current-season`) → tiltva, az üzenetben a következő év január 1-je.
9. Eredmény jövőbeli eseményen (`previous-season-moved`) → tiltva, „az eseményeket a nullázás után írd át”.
10. Nincs mit nullázni (`empty`) → „Nincs mit nullázni”, tiltva.
11. Csak tippek vannak (`bets-only`) → archív nélkül is engedélyezett, és a nullázás a tippeket törli.
12. Jövőbeli eseményre már van tipp → figyelmeztetés a darabszámmal, de nem tilt.

**Megerősítő kód** (`previous-season`, mindegyiknél az `app:e2e:counts` változatlan)
13. Rossz kód → hibaüzenet, semmi nem törlődött, az oldalon új, eltérő kód van.
14. Üres kód → elutasítva.
15. Elavult kód: az oldal betöltése (A kód), újratöltés (B kód), majd az A kód beküldése → elutasítva.
16. Egyszer használható: a jó kódot kétszer küldik be, például a böngésző „vissza” és újraküldés gombjával → a második elutasítva. Ha addig új adat került be, az megmarad.
17. Hiányzó vagy hamis CSRF-token (közvetlen kérés) → elutasítva.

**Sikeres nullázás** (`previous-season`)
18. Helyes kód → siker-üzenet a ténylegesen törölt darabszámokkal. Az `app:e2e:counts` szerint:
   - `bet`, `bet_attribute`, `result`, `result_attribute`, `alternative_championship`, `trophy` üres;
   - a userek `point_summary`-je 0, a többi pontmezőjük NULL;
   - **változatlan:** események (darab és dátum), userek és a `is_alter_champs` jelölésük, pilóták a pontjaikkal, `championship_point`, nyeremény és díjsorok, információk, beállítások, üzenőfal.
19. A front oldal a nullázás után, a cache-ek ürítése miatt azonnal:
   - az oldalsáv VB-tabellájában mindenki 0 ponttal, kupa nélkül;
   - a Trófeák oldal üres;
   - az Aktuális oldalon nincs régi tipp vagy eredmény;
   - az Eredmények oldalon a tavalyi fül az archív bejegyzést mutatja;
   - nincs 5xx és nincs konzolhiba (`seeNoServerErrors`, `seeNoConsoleErrors`).
20. A nullázás után az új szezon működik: a user tippel az idei eseményre, az admin eredményt rögzít, és a pontok, a VB-tabella és a kupák helyesen számolódnak.
21. A nullázás után a „Pontok újraszámolása” gomb hiba nélkül lefut, és nem hoz vissza régi adatot.
22. A nullázás után az oldal újra megnyitva: „Nincs mit nullázni” (nincs maradék adat).

### Unit tesztek (csak kiegészítés)

- Hiba a tranzakció közben (a DBAL kapcsolat mockolva, a 4. `DELETE` kivételt dob) → rollback, a cache-ürítés nem fut, a kivétel felmegy. Ezt e2e-ben csak éles kódba tett teszt-kapcsolóval lehetne előidézni, azt nem akarjuk.
- `RAPIDAPI_KEY` üres → ✗ (az e2e env-ben a kulcs mindig be van állítva).
- A kódgenerálás formátuma (mindig 6 jegy, nullákkal kiegészítve).

## A megvalósító döntései (felülbírálhatók)

- **A pilótapontokat (`driver.point`) a nullázás nem nullázza** (eredetileg javasolt volt). A tippválasztó ezek szerint rendez és ezeket mutatja (`Repository/Driver::getDriverChoices`, `BettingFormPresenter`). A szinkron csak az első futam után ad idei pontot, így a nullázás után az első hétvégén minden pilóta 0 ponttal, ömlesztve jelenne meg. A tavalyi sorrend ennél jobb, az újoncok úgyis 0-val indulnak. A pilótapont-szinkron ezért kézi emlékeztető lett.
- A jövőbeli eseményre eső eredmény tiltó feltétel (lásd fent).
- A `ResultCache` ürítése is a nullázás része.
- **Élesítés előtt egyszer ellenőrizni kell az éles DB-n**, hogy az érintett táblák InnoDB-k: `SHOW TABLE STATUS WHERE Name IN ('bet','bet_attribute','result','result_attribute','alternative_championship','trophy','user')`. MyISAM táblán nincs tranzakció, így a „minden vagy semmi” nem teljesülne. Lokálisan mind InnoDB.

## Ami a megvalósítóra marad

- A darabszámok pontos SQL-jei, a DTO formája, a template részletes kinézete (EasyAdmin/Bootstrap osztályok).
- A 2LC-ürítés pontos API-ja (`Cache::evictEntityRegions()` / `evictCollectionRegions()` / `evictQueryRegions()`).
- A linkek az admin menükre: `AdminUrlGenerator`.

## Utómunka

- Az Obsidian „Évváltási checklist (szezonváltás)” jegyzet rövidül: az admin oldalra mutat, és a háttérmagyarázatot (miért, buktatók) tartja meg.
- A CLAUDE.md Notes része egy sort kap az oldalról.

## További automatizálási ötletek (nem ennek a körnek a része)

- Az archív Információ-bejegyzés generálása gombnyomásra a mostani Eredmények tabellából (ez a legnagyobb kézi lépés).
- A Szabályzat pontértékei a valós beállításokból generálódjanak.
- Pilótaszinkron: ugyanaz a „még üres → tavalyi” visszaesés, mint a cronban.
- Az Eredmények oldal évfülei az adatbázisból jöjjenek, ne a böngésző órájából.
