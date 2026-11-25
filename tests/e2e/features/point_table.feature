# language: hu
@point_table
Jellemző: Bajnoki pontozási tábla az adminban
  A hétvégi helyezés -> bajnoki pont tábla (championship_point) az admin "Bajnoki pontok" menüjében szerkeszthető.
  A pontszámítás táblázata "helyezés:pont" párokban (az oldalsáv "Pontok" modulja).
  Az e2e seed a mostani éles táblát tölti be: 15, 11, 9, 7, 6, 5, 4, 2, 1, 0 (src/E2e/E2eFixtures.php: POINT_TABLE).
  Mentéskor (új / módosított / törölt sor) a már kiszámolt hétvégék az új tábla szerint újrapontozódnak,
  a tárolt hétvégi összpontokból. A seed sorainak id-ja a helyezés (1–10).
  A lezajlott hétvége (results-entered): User1 70, User2 67, User3 15 hétvégi pont.

  Forgatókönyv: Lezárt hétvége után átírt tábla: a hétvége, a VB tabella és a pontinfó az új tábla szerint
    Amennyiben lezajlott hétvége van eredményekkel
    Ha a szuperadmin átírja a bajnoki pontokat:
      | helyezés | pont |
      | 1        | 25   |
      | 2        | 18   |
      | 3        | 15   |
    Akkor megjelenik az admin üzenet: "1 kiszámolt hétvége bajnoki pontjai újraszámolva"
    Amennyiben "E2E User1" belép
    Akkor a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 25           |
      | E2E User2 | 67           | 18           |
      | E2E User3 | 15           | 15           |
    És a VB tabella:
      | név       | pont | lemaradás |
      | E2E User1 | 25   |           |
      | E2E User2 | 18   | + 7       |
      | E2E User3 | 15   | + 10      |
    És a pontszámítás táblázata: "1:25 2:18 3:15 4:7 5:6 6:5 7:4 8:2 9:1 10:0", összesen 83 pont
    És a VB modulban a hátralévő pontok: "25 pont szerezhető még (1×25)"

  # 1. hétvége: User1 70, User2 67, User3 15 | 2.: User2 73, User1 64, User3 15 | 3.: User3 73, User1 64, User2 12
  Forgatókönyv: Szezon közben átírt tábla: minden lezárt hétvége újrapontozódik
    Amennyiben szezon közben vagyunk: 3 lezajlott hétvége eredménnyel, a 4. jövő héten
    Ha a szuperadmin átírja a bajnoki pontokat:
      | helyezés | pont |
      | 1        | 25   |
    Akkor megjelenik az admin üzenet: "3 kiszámolt hétvége bajnoki pontjai újraszámolva"
    Amennyiben "E2E User1" belép
    Akkor az Eredmények oldalon a 2. nagydíj összesítője:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 25           |
      | E2E User1 | 64           | 11           |
      | E2E User3 | 15           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás |
      | E2E User1 | 47   |           |
      | E2E User2 | 45   | + 2       |
      | E2E User3 | 43   | + 4       |

  Forgatókönyv: Az eredmények előtt átírt tábla szerint számol az eredmény rögzítése
    Amennyiben mindhárom játékos tippelt, de még nincs eredmény
    Ha a szuperadmin átírja a bajnoki pontokat:
      | helyezés | pont |
      | 1        | 30   |
      | 2        | 20   |
    Akkor megjelenik az admin üzenet: "0 kiszámolt hétvége bajnoki pontjai újraszámolva"
    Ha az admin rögzíti az "Időmérő" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És az admin rögzíti a "Futam" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB", safety car: "Igen"
    És "E2E User1" belép
    Akkor a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 30           |
      | E2E User2 | 67           | 20           |
      | E2E User3 | 15           | 9            |

  # User1 és User2 egyaránt 70 pont: mindketten az 1. hely pontját kapják, User3 a 3. helyét
  Forgatókönyv: Holtverseny az új táblával: azonos pont, a következő helyezés kimarad
    Amennyiben lezajlott hétvége van eredményekkel, E2E User1 és E2E User2 holtversenyben
    Ha a szuperadmin átírja a bajnoki pontokat:
      | helyezés | pont |
      | 1        | 25   |
      | 2        | 18   |
      | 3        | 15   |
    És "E2E User1" belép
    Akkor a VB tabella, sorrendtől függetlenül:
      | név       | pont | lemaradás |
      | E2E User1 | 25   |           |
      | E2E User2 | 25   |           |
      | E2E User3 | 15   | + 10      |

  Forgatókönyv: Törölt helyezés 0 bajnoki pontot ér
    Amennyiben lezajlott hétvége van eredményekkel
    Ha a szuperadmin törli a 3. helyezés bajnoki pontját
    Akkor megjelenik az admin üzenet: "1 kiszámolt hétvége bajnoki pontjai újraszámolva"
    Amennyiben "E2E User1" belép
    Akkor a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 15           |
      | E2E User2 | 67           | 11           |
      | E2E User3 | 15           | 0            |
    És a pontszámítás táblázata: "1:15 2:11 4:7 5:6 6:5 7:4 8:2 9:1 10:0", összesen 51 pont

  Forgatókönyv: Már létező helyezés és negatív pont nem menthető
    Amennyiben lezajlott hétvége van eredményekkel
    Ha a szuperadmin felvesz egy bajnoki pontot: 2. helyezés, 5 pont
    Akkor a mentés hibát jelez: "Ehhez a helyezéshez már van pont megadva."
    Ha a szuperadmin felvesz egy bajnoki pontot: 11. helyezés, -1 pont
    Akkor a mentés hibát jelez: "A pont nem lehet negatív."
    Amennyiben "E2E User1" belép
    Akkor a pontszámítás táblázata: "1:15 2:11 3:9 4:7 5:6 6:5 7:4 8:2 9:1 10:0", összesen 60 pont
