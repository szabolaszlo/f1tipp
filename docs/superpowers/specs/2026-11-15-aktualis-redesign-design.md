# Aktuális oldal és keret – „TV-grafika” dizájnfrissítés

Dátum: 2026-11-15 · Állapot: tervezet · Látványterv: [`2026-11-15-aktualis-redesign-mockup.html`](2026-11-15-aktualis-redesign-mockup.html) (böngészőben megnyitható, reszponzív)

## Miért

Az F1Tipp mostani kinézete régies, és a legtöbben mobilról nézik. Mobilon jelenleg minden egymás alá esik: kb. 3500 px hosszú az oldal, a tipptáblák vízszintesen kilógnak, a háttérkép és a sok kiemelt szín nehezen olvasható. A cél egy formula1.com-szerű, modern, sötét arculat. Az eredménytáblák a TV-közvetítések eredménygrafikáit idézzék (helyezés · név · pont, kompakt sorokban). Mobile first, de desktopon se essen szét.

## Célok

- Az **Aktuális oldal** (`/#/actual`) és a **közös keret** (fejléc, menü, oldalsáv, lábléc) új dizájnt kap a végleges látványterv (v11) szerint.
- Mobilon nincs vízszintes görgetés 360 px szélességtől felfelé. Desktopon két hasáb: fő tartalom és jobb oldalsáv.
- Az **Eredmények oldal** (`/module/results`, `ResultsController`) is az új táblákat kapja, mert ugyanazokat a `full`/`summary` sablonokat rendereli (a felhasználó döntése). A `ResultsController` FileCache-e (`'results' . count($results)` kulcs) a régi HTML-t tárolja, ezért telepítéskor ürítendő. Ehhez kell egy deploy lépés vagy a kulcs verziózása, pl. `'results_v2_' . count(...)`.
- A többi oldal (Tippelés, Naptár, Szabályok, Statisztika, Belépés) megkapja az új fejlécet és a sötét alapot, plusz egy illesztő réteget, hogy a régi Bootstrap 3-as tartalom olvasható maradjon rajta.

## Nem célok

- A többi oldal tartalmának újratervezése, az Eredmények oldal tábláin kívül. Ez egy későbbi, külön lépés.
- Az Aktuális oldal Vue + JSON API-ra állítása („B irány”). Ez parkolva van, a jegyzet Obsidianban: `Projects/F1Tipp/2026-11-15 Aktuális oldal – Vue + JSON API átállás (B irány).md`
- Pontszámítási, sorrend- vagy kupalogika változtatása.
- A Bootstrap 3 eltávolítása vagy cseréje.

## Anti-célok (ha ez történik, akkor rossz, még ha működik is)

- Bármelyik pontszám, helyezés, kupa vagy „matek” más lesz, mint most.
- Megszűnik az élő frissítés (`module_reload.js`): a táblák nem frissülnek maguktól új tipp vagy eredmény után.
- A többi oldal használhatatlanná válik a sötét alap miatt (pl. fekete szöveg fekete háttéren, olvashatatlan űrlapmezők a Tippelés oldalon).
- Túl erős kontraszt vagy túl hangsúlyos jelölések. A felhasználó kifejezetten visszafogott megjelenést kért.

## Választott megközelítés

| | Megközelítés | Mérlegelés |
|---|---|---|
| **A ✅** | **Csak a megjelenés cseréje:** a Twig sablonok HTML-jét és a CSS-t írjuk újra, az adatlogika és az élő frissítés marad. | Kicsi a kockázat, a logika érintetlen, a frissítés magától megy. Hátrány: a Twig és a Vue kettőssége megmarad. |
| B | Vue komponensek JSON API-ból (az elkezdett `src/Controller/Api/ActualController.php` befejezése). | Ez a Vue-migráció iránya, de sokkal nagyobb munka: új API-k kellenek, a Twig logikáját újra kell írni, és új frissítési mechanizmus kell. Parkolva. |
| C | Előbb A, aztán B. | Gyakorlatilag A most, B később. |

A felhasználó az **A**-t választotta.

## Vizuális döntések (a felhasználóval egyeztetve)

- **Téma:** sötét „TV-grafika”, **visszafogott kontraszt**. Háttér `#14141b`, panel `#1b1b24`, szöveg törtfehér `#e6e6ec`, piros akcentus `#e10600`. A háttérkép (`background2.jpg`) megszűnik.
- **Fejléc:** sötét, nem piros. Logó „F1**TIPP**”, piros „TIPP” résszel. Desktopon vízszintes menü és jobbra a felhasználó (belépés/kilépés). Mobilon a logó, a hamburger és alatta egy vízszintesen görgethető menüsor.
- **Panelfejléc:** az alapnál kicsit világosabb sötét sáv. Bal szélén 4 px piros csík és kis piros nagybetűs címke (pl. FUTAM, IDŐMÉRŐ, HÉTVÉGE, VB, F1), mellette fehér, vastag cím, jobbra halvány meta (dátum, „Tippek 9/11”). Nincs fehér sáv és nincs piros ferde címke.
- **Sorok:** helyezés · név (nagybetűs) · számok jobbra igazítva, csíkozott sorok vékony elválasztóval.
- **Csapatszín:** csak valódi pilótáknál és csapatoknál (pilóta-VB és konstruktőri VB sorainak bal csíkja, valamint a tipptábla „Eredmény” sorának chipjei). A tippelők soraiban és a tippelők tippjeinél **nincs** csapatszín.
- **Dobogós színezés** (tompa arany, ezüst, bronz helyezésszám): csak a hétvégi összesítőben és az egyéni pontversenyben. **A tipptáblákban nincs.**
- **A belépett felhasználó sora nincs kiemelve.**
- **Tippchipek** (`bet-high` / `bet-low` / üres), nagyon enyhe jelöléssel:
  - pontos hely: **F1 szektor-lila** (`#b44cf2`) vékony keret, lilás betű, alig látható lila háttér
  - rossz helyen eltalált tipp: **F1 szektor-sárga** (`#f5d000`), halvány, szaggatott keret és sárgás betű
  - (2026-11-16: a zöld/homokszín helyett a felhasználó kérésére az F1 szektoridő-színek)
  - nem talált: halványszürke betű, alapkeret

  Pontos értékek a látványterv CSS-ében: `.chip.exact`, `.chip.near`.
- **Az SC (Igen/Nem) tipp** ugyanolyan chip, a tippek sorában utolsóként, ugyanazzal a jelöléssel.
- **Jelmagyarázat** minden tipptábla alján, az eseménytípus pontértékeivel (a szabályból számolva, nem beégetve).
- **Kupák:** a meglévő kupa SVG (`trophy_{gold,silver,bronze}.svg`), sötét témához tompított színnel, 24 px, a darabszám a kupa belsejében.
- **Betűtípus:** Titillium Web (Google Fonts).

## Az Aktuális oldal tartalma és sorrendje

Fülek nincsenek, **minden tábla egymás alatt** van, a mostani sorrendben: ha van futameredmény, a hétvégi összesítő áll legfelül, utána az események az időtávolság szerint (`ActualEventsRuntimeExtension::renderActualEvents`, nem változik).

1. **Hétvégi oldalfejléc** (új), csak meglévő adatból: forduló (`weekend_order`), az esemény neve (`Event::name`, pl. „ABU DHABI”) és a hétvége dátumtartománya. Helyszín vagy „Nagydíj” megnevezés nincs, mert nincs rá adat, és sémát nem módosítunk. *(Végrehajtói döntés.)* Az Aktuális tartalmán belül, a bal hasábban jelenik meg, nem a két hasáb fölött.
2. **Hétvégi összesítő** (`summary.html.twig`). Oszlopok: Tippelő · **Hétvégi pont** (`collectedPoints`, halványabb) · **Bajnoki pont** (`points`, hangsúlyos, „+14” formában, 0-nál „0”). Holtversenynél azonos helyezésszám (azonos hétvégi pont esetén).
3. **Eseménytáblák** (`full.html.twig`, Futam / Időmérő / Sprint). Felül egy oszlopfejléc (1.…N., SC), aztán az „Eredmény” sor csapatszínes chipekkel, majd a tippelők pont szerint csökkenő sorrendben. Desktopon egy sor: helyezés · név · chipek · pont. Mobilon név és pont, alatta teljes szélességben a chipek. A pozíciók száma eseménytípustól függ (futam és időmérő 10, sprint 8, futamnál plusz SC).
4. **Esemény előtti állapotok** ugyanabban a panelstílusban:
   - `only_users`: ki tippelt eddig (névlista)
   - `only_bets`: a tippek eredmény nélkül, semleges chipekkel, eredménysor és jelmagyarázat nélkül
5. **Pontverseny-grafikon** (`point_summary_chart_js.html.twig`): marad, sötét témához igazított Chart.js színekkel, panelstílusban.
6. **Üzenőfal** (`messageWallExtension`): marad, panelstílusban.
7. **Hírek** (`News.vue`): marad, panelstílusban.

## Oldalsáv (desktopon jobb hasáb, mobilon a fő tartalom alatt)

1. **Hétvégi kupák** (`trophies.html.twig`): kupaikon · nevek · pont, panelstílusban.
2. **Egyéni pontverseny** (`user_championship.html.twig`): helyezés (dobogós színnel) · név · kupák számmal · online pötty · kockás zászló · hátrány · pont. Alatta:
   - a „Matek” két nagy számmal (hátralévő hétvégék, még szerezhető pont)
   - a rekordok (`best_of_*`) és a magyarázó szövegek (holtverseny szabálya, online pötty, kockás zászló), halványan, kisebb betűvel
3. **Nyeremény** (`prize_draw.html.twig`) és **Pontátváltás** (`point_calculating_info.html.twig`): panelstílusban, tartalmuk nem változik.
4. **Pilóta VB és Konstruktőri VB** (`ChampionshipResult.vue`): helyezés · csapatszín-csík · vezetéknév + halvány keresztnév · pont. A „wins” oszlop megmarad, halványan.

Desktopon az oldalsáv görgetéskor helyben marad, ha elfér a képernyőn. Ha nem fér el, egyszerűen görgetődik.

*(Végrehajtói döntések.)*
- Az oldalsáv, az üzenőfal, a grafikon és a hírek **ugyanúgy minden oldalon megjelennek, mint most**, mert a `base.html.twig`-ben, a Vue route-on kívül vannak. Ez tiszta restyle, route-függő elrejtés nincs.
- **Nincsenek lenyitható részek:** a látványtervben lévő „Teljes tabella ▾” és „Konstruktőrök ▸” csak a minta rövidítése volt. Az egyéni pontverseny minden tippelővel, a rekordok és a magyarázatok, valamint a pilóta- és a konstruktőri VB teljes egészében látszik, ahogy most. Így nem kell nyitott/zárt állapotot megőrizni a `module_reload.js` újratöltésein át.

## Technikai keretek

- **Élő frissítés:** a `.reload-able-module` wrapperek és `data-module-*` attribútumaik változatlanok maradnak (`base.html.twig`, `actual.html.twig`). A `/module/<route>` válaszok az új markupot adják.
- **CSS:** új téma stíluslap (`assets/css/theme.css`) design tokenekkel (`:root` változók), a Bootstrap 3 és az `app.css` után betöltve. A régi `app.css`/`bet.css` szabályok közül az ütközőket felülírjuk vagy töröljük. A `bet-high`/`bet-low` osztálynevek a backendből jönnek, ezekre épül a chip stílus.
- **Csapatszínek:** a `Driver` entitásnak nincs csapat mezője. A pilóta rövid kódja (pl. `VER`) → csapat → szín leképezés egy szezononként frissítendő konfigurációban él (pl. Symfony paraméter `config/packages/f1tipp_teams.yaml`), és a Twig és a Vue is ebből kapja. Konstruktőröknél a csapatnév alapján. Ismeretlen kódnál semleges szürke.
- **Navigation.vue:** a Bootstrap navbar és a jQuery collapse helyett saját markup és CSS. A belépés/kilépés és a felhasználónév megmarad.
- **Elrendezés:** a `base.html.twig` `col-1/col-7/col-3/col-1` rácsa helyett CSS grid (desktopon `1fr 380px`, max. 1280 px, 900 px alatt egy hasáb).
- **Többi oldal illesztése:** a `.panel`, `.table`, `.form-control`, `.btn`, `select2` alapstílusa sötét témára igazítva, hogy olvashatóak legyenek.

## A végrehajtóra bízva

- A pontos CSS-értékek finomhangolása a látványterv alapján, az osztálynevek és a Twig blokkok szervezése.
- A sprint és a ritkább esetek (nincs eredmény, nem belépett felhasználó, üres hétvége, `no_results`) megjelenése a fenti stílusban.
- Az ikonok (`macro/icon.html.twig`) megtartása vagy elhagyása a panelfejlécben.

## Ellenőrzés

- Kézi ellenőrzés Playwrighttal 375 px és 1366 px szélességben az Aktuális oldalon (eredmény után és eredmény előtt), az Eredmények oldalon (több múltbeli hétvége, sprinthétvége is) és a többi oldalon (olvashatóság).
- A pontszámok és sorrendek összevetése a mostani oldallal ugyanazon az adatbázison.
- Az élő frissítés kipróbálása: új tipp vagy eredmény után a blokk magától frissül.
