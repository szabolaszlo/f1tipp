# Nyeremény modul adminból szerkeszthetően – terv

> Forrás: Obsidian `Projects/F1Tipp/2026-11-23 Nyeremény modul adminból – terv.md` (a jegyzet egyben spec és terv).
> A feladatok fejléce „Task N” a superpowers skill scriptjei miatt; a tartalom a jegyzeté.
> Branch: `extract-static-points`. Minta: a bajnoki pontozási tábla (`docs/superpowers/plans/2026-11-23-championship-point-table.md`).

## Cél és miért

A nyeremény modul (`templates/extension/prize_draw.html.twig`) összegei és a megjegyzés sora be vannak égetve a sablonba. Minden évben kódot kell módosítani és deployolni.

A cél: az adminban szerkeszthető legyen, és legyen egy **„Még nyitott a nevezés”** kapcsoló. Évelején, amíg a nevezés tart, nem tudni, hányan lesznek, ezért a díjazást még nem lehet kiszámolni.

## Döntések (2026-11-23)

- **B változat: kézi.** Helyezés → összeg sorok, plusz egy szabad szöveges megjegyzés. A rendszer nem számol semmit.
- **„Még nyitott a nevezés” jelölőnégyzet.** Ha be van pipálva:
  - **nem** látszanak a helyezések és összegek, és a megjegyzés sem;
  - helyette csak ennyi: **„Jelenleg X résztvevő van, a díjazás még nincs kiszámolva.”**
  - X = azoknak a felhasználóknak a száma, akiknél `is_alter_champs = true`. Mindig élőben számolva.
- A számolt változat (A) elvetve.
- Nem cél: a kupapontok és a tippenkénti pontok kiemelése.

## Megoldás

### Adatmodell

- **`PrizeDraw`** entitás, egyetlen sorral (singleton, id = 1), tábla `prize_draw`:
  - `entryOpen` (bool, alapból false): „Még nyitott a nevezés”;
  - `note` (text, nullable): a megjegyzés sor;
  - `prizes`: OneToMany a `Prize`-ra, `cascade: ['persist', 'remove']`, `orphanRemoval: true`, rendezés place ASC.
- **`Prize`** entitás, tábla `prize`:
  - `id`, `prizeDraw` (ManyToOne, NOT NULL), `place` (int), `amount` (int, forint);
  - egyedi index `(prize_draw_id, place)` explicit névvel (`UNIQ_PRIZE_DRAW_PLACE`).
- Mindkettő `#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]`, a kollekció is cache-elt.
- Validáció:
  - `Prize`: `place` Positive, `amount` PositiveOrZero, NotNull mindkettőn;
  - `PrizeDraw`: `Assert\Valid` a kollekción, és egy `Assert\Callback`, ami a kollekción belüli duplikált helyezést kiszűri.

### Admin

- `PrizeDrawCrudController`, ROLE_SUPER_ADMIN (`setEntityPermission`):
  - csak `edit` és `detail`; a `new`, `delete` és `batchDelete` letiltva (singleton);
  - mezők: `BooleanField entryOpen` (`renderAsSwitch(false)`), `TextareaField note`, `CollectionField prizes` `->useEntryCrudForm(PrizeCrudController::class)`, `allowAdd`, `allowDelete`.
  - Help: „Ha a nevezés még nyitott, a modul csak a résztvevők számát mutatja.”
- `PrizeCrudController`: csak az entry formhoz (place, amount). Menüben nem szerepel.
- Menüpont: `MenuItem::linkToCrud('admin_prize_draw', 'fas fa-trophy', PrizeDraw::class)->setAction(Action::EDIT)->setEntityId(1)`, a „Bajnoki pontok” után.
- Mentés után flash: „Nyeremény mentve”.
- Fordítások: `translations/messages.hu.xlf`.

### Megjelenítés

- `PrizeDrawRuntimeExtension`: DI-ből a `PrizeDraw` repository és a `UserRepository`; a sablon a `prizeDraw`-t, és ha `entryOpen`, a `participantCount`-ot kapja.
- Új: `UserRepository::countAlternativeChampionshipUsers(): int` (`COUNT`).
- Csak belépett usernek renderel.
- `prize_draw.html.twig`: nyitott nevezésnél csak a résztvevőszám-sor; lezártnál a mostani kinézet, `amount|number_format(0, '.', ',')`, `f1-pos--p{{ place }}`, megjegyzés csak ha nem üres.
- Ha a `prize_draw` sor hiányzik: a modul ne dobjon hibát, ne jelenjen meg.

### Éles SQL

`docs/sql/2026-11-23-prize-draw.sql`: CREATE TABLE `prize_draw` és `prize` (InnoDB, utf8mb4_unicode_ci, FK, `UNIQ_PRIZE_DRAW_PLACE`), INSERT a mostani értékekkel (id 1, entry_open 0, note; 1→24000, 2→14400, 3→9600). Lokálisan is ezzel; `doctrine:schema:update --dump-sql` ne mutasson eltérést. Élesen feltöltés előtt.

## Global Constraints

- Nincs Doctrine migration, a séma kézi SQL-lel változik.
- A meglévő `.feature` fájlok és elvárásaik nem változnak.
- Az összegek formátuma marad: `24,000`.
- A modul csak belépett usernek látszik.

## Review Focus

1. Duplikált helyezés egy űrlapon belül (két új sor, mindkettő 1.): validációs hiba legyen, ne 500.
2. Minden sor törlése, lezárt nevezéssel: a modul ne törjön.
3. Hiányzó `prize_draw` sor: ne legyen 500 az oldalsávban (minden oldalon renderelődik).
4. A résztvevőszám frissüljön, ha az adminban valakit beraknak a bajnokságba vagy kivesznek belőle (2LC query cache a `COUNT`-nál).
5. A singleton CRUD-ból ne lehessen új `PrizeDraw`-t létrehozni, vagy az egyetlent törölni (közvetlen URL-lel sem).

## Task 1: Entitások, SQL, megjelenítés (adminnal még nem)

- Új: `src/Entity/PrizeDraw.php`, `src/Entity/Prize.php`, `src/Repository/PrizeDrawRepository.php`, `docs/sql/2026-11-23-prize-draw.sql`.
- Módosul: `src/Repository/UserRepository.php`, `src/Twig/PrizeDrawRuntimeExtension.php`, `templates/extension/prize_draw.html.twig`, `src/E2e/E2eFixtures.php`.
- E2E seed: `E2eFixtures::PRIZES = [1 => 24000, 2 => 14400, 3 => 9600]`, `PRIZE_NOTE = '12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000'`, entryOpen false; a `PrizeDraw` id-ja 1.
- Bukó teszt előre: e2e forgatókönyv „belépve a Nyeremény modul a seed értékeit mutatja” (Task 3, 1. forgatókönyv).
- Ellenőrzés: `php bin/phpunit` és `@weekend|@season` e2e zöld. Commit.

## Task 2: Admin (singleton CRUD + kollekció + validáció)

- Új: `src/Controller/Admin/PrizeDrawCrudController.php`, `src/Controller/Admin/PrizeCrudController.php`.
- Módosul: `DashboardController.php`, `translations/messages.hu.xlf`.
- Bukó tesztek előre: a Task 3 admin forgatókönyvei.
- Ellenőrzés: kézzel lokálon is (`http://127.0.0.1:8000/admin`): checkbox, sor hozzáadása, sor törlése.

## Task 3: E2E `tests/e2e/features/prize_draw.feature` (`@prize_draw`)

Az e2e userek közül 3 bajnoksági tag (E2E User1–3), az admin és a szuperadmin nem.

1. Seed értékek: „E2E User1” belép → 1. 24,000, 2. 14,400, 3. 9,600; megjegyzés: „12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000”.
2. Összeg és megjegyzés átírása: 1. hely 30000, új megjegyzés → „30,000” és az új megjegyzés.
3. Sor hozzáadása és törlése: új 4. hely 5000 → „4.” sor; a 3. sor törlése → eltűnik.
4. Nyitott nevezés: „Jelenleg 3 résztvevő van, a díjazás még nincs kiszámolva.”; nincs helyezés-sor és megjegyzés.
5. Résztvevőszám élőben: új bajnoksági tag user → 4; valaki kivéve a bajnokságból → 3.
6. Visszakapcsolás: a pipa kivétele után ismét az összegek és a megjegyzés.
7. Validáció: két sor ugyanazzal a helyezéssel → hibaüzenet, nem 500, a modul változatlan; negatív összeg → hibaüzenet.
8. Vendég nem látja a modult (ha még nincs ilyen ellenőrzés máshol).

Új lépések: `admin_steps.js` (nyeremény beállítások megnyitása, mezők, sor hozzáadás/törlés, mentés), `SidebarPage.prizeDraw()`, modul-ellenőrző lépések. Végén a teljes e2e (`npm run e2e`) és a PHPUnit zöld. Mutációs próba: az `entryOpen` ág kikapcsolásával a 4–5. forgatókönyv bukjon.

## Task 4: Dokumentáció

- CLAUDE.md Notes: a nyeremény az adminban („Nyeremény” menü, singleton `PrizeDraw` id = 1), a résztvevőszám az `is_alter_champs` userekből.
- Obsidian „Évváltási checklist (szezonváltás)” „Nyeremény” pontja: „admin → Nyeremény; évelején »Még nyitott a nevezés« be, a nevezés lezárásakor ki, és az összegek kitöltése”.
- Az éles SQL átadása a végén (a `championship_point` SQL-lel együtt).
