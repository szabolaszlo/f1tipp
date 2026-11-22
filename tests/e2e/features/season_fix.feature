# language: hu
@season
Jellemző: Késői eredményjavítás szezon közben
  Az 1. hétvége időmérő eredménye két másik hétvége után derül ki rosszul. Az admin átírja, és a pontoknak,
  a hétvégi összesítőnek, a bajnoki tabellának és a kupáknak újra kell számolódniuk – a többi hétvégét viszont
  nem szabad érintenie. Minden hétvége 2 eseményes (időmérő + futam); a sprintes hétvégét is tartalmazó
  szezont a sprint_season_fix.feature fedi.
  A seed (src/E2e/E2eScenarios.php: season): 3 lezajlott hétvége, az eredmény mindig időmérő
  VER NOR LEC PIA HAM RUS SAI ALO GAS ALB, futam ugyanez + safety car: Igen; a 4. hétvége jövő héten.
    1. hétvége: User1 40 + 30 = 70, User2 34 + 33 = 67, User3 6 + 9 = 15
    2. hétvége: User1 34 + 30 = 64, User2 40 + 33 = 73, User3 15
    3. hétvége: User1 34 + 30 = 64, User2 6 + 6 = 12,   User3 40 + 33 = 73
  A Trófeák oldalon a kupák pontja: arany 25, ezüst 18, bronz 15.

  Forgatókönyv: A szezon állása hétvégénként számolódik
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, a 4. jövő héten
    És "E2E User1" belép
    Akkor az Eredmények oldal nagydíjai:
      | nagydíj | név            |
      | 1       | E2E 1. Nagydíj |
      | 2       | E2E 2. Nagydíj |
      | 3       | E2E 3. Nagydíj |
    És az Eredmények oldalon az 1. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 15           |
      | E2E User2 | 67           | 11           |
      | E2E User3 | 15           | 9            |
    És az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User3 | 15           | 9            |
    És az Eredmények oldalon a 3. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User3 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User2 | 12           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupák                     |
      | E2E User1 | 37   |           | 1 arany, 2 ezüst          |
      | E2E User2 | 35   | + 2       | 1 arany, 1 ezüst, 1 bronz |
      | E2E User3 | 33   | + 4       | 1 arany, 2 bronz          |
    És a Kupák modulban az "E2E 3. Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User3 | 73   |
      | ezüst | E2E User1 | 64   |
      | bronz | E2E User2 | 12   |
    És a Trófeák oldal kupatáblája:
      | név       | pont |
      | E2E User1 | 61   |
      | E2E User2 | 58   |
      | E2E User3 | 55   |

  # a javítás után: 1. hétvége User1 34 + 30 = 64, User2 40 + 33 = 73
  Forgatókönyv: Két hétvégével későbbi javítás az egész szezont újraszámolja
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, a 4. jövő héten
    Ha az admin az 1. hétvége "Időmérő" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    És "E2E User1" belép
    Akkor a pontok az Eredmények oldalon az 1. nagydíjon:
      | név       | IDŐMÉRŐ |
      | E2E User1 | 34      |
      | E2E User2 | 40      |
    # az 1. hétvége változik, a 2. és a 3. nem
    És az Eredmények oldalon az 1. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User3 | 15           | 9            |
    És az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User3 | 15           | 9            |
    És az Eredmények oldalon a 3. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User3 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User2 | 12           | 9            |
    # User1 és User3 33-33 ponton: a több arany (User3: 1, User1: 0) dönt
    És a VB tabella:
      | név       | pont | lemaradás | kupák            |
      | E2E User2 | 39   |           | 2 arany, 1 bronz |
      | E2E User3 | 33   | + 6       | 1 arany, 2 bronz |
      | E2E User1 | 33   | + 6       | 3 ezüst          |
    # az 1. hétvége javítása nem írja felül a "legutóbbi hétvége" dobogóját
    És a Kupák modul az "E2E 3. Nagydíj" dobogóját mutatja
    És a Trófeák oldal kupatáblája:
      | név       | pont |
      | E2E User2 | 65   |
      | E2E User3 | 55   |
      | E2E User1 | 54   |

  Forgatókönyv: Az összes pont újraszámolása az adminból nem változtat a szezonon
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, a 4. jövő héten
    Ha az admin újraszámolja az összes pontot
    Akkor megjelenik az admin üzenet: "Sikeresen újraszámolva a pontok"
    És a VB tabella:
      | név       | pont | lemaradás | kupák                     |
      | E2E User1 | 37   |           | 1 arany, 2 ezüst          |
      | E2E User2 | 35   | + 2       | 1 arany, 1 ezüst, 1 bronz |
      | E2E User3 | 33   | + 4       | 1 arany, 2 bronz          |
    És az Eredmények oldalon az 1. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 15           |
      | E2E User2 | 67           | 11           |
      | E2E User3 | 15           | 9            |
    És az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User3 | 15           | 9            |
    És az Eredmények oldalon a 3. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User3 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User2 | 12           | 9            |
