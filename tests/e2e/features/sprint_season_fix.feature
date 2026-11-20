# language: hu
@season-sprint
Jellemző: Késői eredményjavítás szezon közben, sprintes hétvégével
  A season_fix.feature párja: itt az 1. hétvége sprintes (sprint, időmérő, futam), és a sprint eredménye derül ki
  két másik hétvége után rosszul. Az admin átírja, és a pontoknak, a hétvégi összesítőnek, a bajnoki tabellának
  és a kupáknak újra kell számolódniuk – a többi hétvégét viszont nem szabad érintenie.
  A seed (src/E2e/E2eScenarios.php: sprint-season): mint a season, de az 1. hétvégén sprint is van;
  a sprint eredmény NOR VER LEC PIA HAM RUS SAI ALO, a sprint tippek mint a sprint_weekend.feature-ben.
    1. hétvége: User1 4 + 40 + 30 = 74, User2 8 + 34 + 33 = 75, User3 0 + 6 + 9 = 15
    2. hétvége: User1 34 + 30 = 64, User2 40 + 33 = 73, User3 15
    3. hétvége: User1 34 + 30 = 64, User2 6 + 6 = 12,   User3 40 + 33 = 73
  A Trófeák oldalon a kupák pontja: arany 25, ezüst 18, bronz 15.

  Forgatókönyv: A sprintes hétvégét is tartalmazó szezon állása hétvégénként számolódik
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, az 1. sprintes, a 4. jövő héten
    És "E2E User1" belép
    Akkor az Eredmények oldal nagydíjai:
      | nagydíj | név            |
      | 1       | E2E 1. Nagydíj |
      | 2       | E2E 2. Nagydíj |
      | 3       | E2E 3. Nagydíj |
    És a pontok az Eredmények oldalon az 1. nagydíjon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 4            | 40      | 30    |
      | E2E User2 | 8            | 34      | 33    |
      | E2E User3 | 0            | 6       | 9     |
    És az Eredmények oldalon az 1. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 75           | 14           |
      | E2E User1 | 74           | 10           |
      | E2E User3 | 15           | 8            |
    És az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 14           |
      | E2E User1 | 64           | 10           |
      | E2E User3 | 15           | 8            |
    És az Eredmények oldalon a 3. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User3 | 73           | 14           |
      | E2E User1 | 64           | 10           |
      | E2E User2 | 12           | 8            |
    # User1 és User3 30-30 ponton: a több arany (User3: 1, User1: 0) dönt
    És a VB tabella:
      | név       | pont | lemaradás | kupák            |
      | E2E User2 | 36   |           | 2 arany, 1 bronz |
      | E2E User3 | 30   | + 6       | 1 arany, 2 bronz |
      | E2E User1 | 30   | + 6       | 3 ezüst          |
    És a Kupák modulban az "E2E 3. Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User3 | 73   |
      | ezüst | E2E User1 | 64   |
      | bronz | E2E User2 | 12   |
    És a Trófeák oldal kupatáblája:
      | név       | pont |
      | E2E User2 | 65   |
      | E2E User3 | 55   |
      | E2E User1 | 54   |

  # a javítás után: 1. hétvége User1 8 + 40 + 30 = 78, User2 4 + 34 + 33 = 71
  Forgatókönyv: A sprint eredmény két hétvégével későbbi javítása az egész szezont újraszámolja
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, az 1. sprintes, a 4. jövő héten
    Ha az admin az 1. hétvége "Sprint Futam" eredményét erre javítja: "LEC PIA VER NOR HAM RUS SAI ALO"
    És "E2E User1" belép
    Akkor a pontok az Eredmények oldalon az 1. nagydíjon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 8            | 40      | 30    |
      | E2E User2 | 4            | 34      | 33    |
      | E2E User3 | 0            | 6       | 9     |
    # az 1. hétvége változik, a 2. és a 3. nem
    És az Eredmények oldalon az 1. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 78           | 14           |
      | E2E User2 | 71           | 10           |
      | E2E User3 | 15           | 8            |
    És az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 14           |
      | E2E User1 | 64           | 10           |
      | E2E User3 | 15           | 8            |
    És az Eredmények oldalon a 3. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User3 | 73           | 14           |
      | E2E User1 | 64           | 10           |
      | E2E User2 | 12           | 8            |
    És a VB tabella:
      | név       | pont | lemaradás | kupák                     |
      | E2E User1 | 34   |           | 1 arany, 2 ezüst          |
      | E2E User2 | 32   | + 2       | 1 arany, 1 ezüst, 1 bronz |
      | E2E User3 | 30   | + 4       | 1 arany, 2 bronz          |
    # az 1. hétvége javítása nem írja felül a "legutóbbi hétvége" dobogóját
    És a Kupák modul az "E2E 3. Nagydíj" dobogóját mutatja
    És a Trófeák oldal kupatáblája:
      | név       | pont |
      | E2E User1 | 61   |
      | E2E User2 | 58   |
      | E2E User3 | 55   |

  Forgatókönyv: Az összes pont újraszámolása az adminból a sprint pontjait is megtartja
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, az 1. sprintes, a 4. jövő héten
    Ha az admin újraszámolja az összes pontot
    Akkor megjelenik az admin üzenet: "Sikeresen újraszámolva a pontok"
    És a pontok az Eredmények oldalon az 1. nagydíjon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 4            | 40      | 30    |
      | E2E User2 | 8            | 34      | 33    |
      | E2E User3 | 0            | 6       | 9     |
    És a VB tabella:
      | név       | pont | lemaradás | kupák            |
      | E2E User2 | 36   |           | 2 arany, 1 bronz |
      | E2E User3 | 30   | + 6       | 1 arany, 2 bronz |
      | E2E User1 | 30   | + 6       | 3 ezüst          |
    És az Eredmények oldalon az 1. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 75           | 14           |
      | E2E User1 | 74           | 10           |
      | E2E User3 | 15           | 8            |
    És az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 14           |
      | E2E User1 | 64           | 10           |
      | E2E User3 | 15           | 8            |
    És az Eredmények oldalon a 3. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User3 | 73           | 14           |
      | E2E User1 | 64           | 10           |
      | E2E User2 | 12           | 8            |
