# language: hu
@scoring
Jellemző: Eredmény és pontszámítás
  2 eseményes hétvége (időmérő + futam); a sprintes hétvégét a sprint_weekend.feature fedi.
  Pontozás: időmérő pontos hely 4 / top 10-ben 1 pont, futam pontos hely 3 / top 10-ben 1 pont, safety car 3 pont;
  a hétvégi sorrend szerinti bajnoki pont 14, 10, 8, …
  A seedelt tippek (src/E2e/E2eScenarios.php: BETS):
    E2E User1: időmérő VER NOR LEC PIA HAM RUS SAI ALO GAS ALB, futam ugyanez, safety car: Nem
    E2E User2: időmérő NOR VER LEC PIA HAM RUS SAI ALO GAS ALB, futam VER NOR LEC PIA HAM RUS SAI ALO GAS ALB, safety car: Igen
    E2E User3: időmérő TSU STR OCO HUL VER NOR LEC PIA HAM RUS, futam ugyanez, safety car: Igen

  # Az admin "új eredmény" űrlapja mindig a következő eredmény nélküli eseményt adja,
  # így az időmérőt és a futamot ebben a sorrendben kell rögzíteni.
  Forgatókönyv: Az admin rögzíti az eredményeket, kiszámolódnak a pontok, a tabella és a kupák
    Amennyiben mindhárom játékos tippelt, de még nincs eredmény
    Ha az admin rögzíti az "Időmérő" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális oldalon:
      | név       | IDŐMÉRŐ |
      | E2E User1 | 40      |
      | E2E User2 | 34      |
      | E2E User3 | 6       |
    És az "IDŐMÉRŐ" táblán "E2E User2" tippjének jelölése: "közeli, közeli, pontos, pontos, pontos, pontos, pontos, pontos, pontos, pontos"
    És az "IDŐMÉRŐ" táblán "E2E User3" tippjének jelölése: "nincs, nincs, nincs, nincs, közeli, közeli, közeli, közeli, közeli, közeli"
    Ha az admin rögzíti a "Futam" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB", safety car: "Igen"
    Akkor a pontok az Aktuális oldalon:
      | név       | FUTAM |
      | E2E User1 | 30    |
      | E2E User2 | 33    |
      | E2E User3 | 9     |
    # az admin nem tagja a bajnokságnak: nincs a tabellában
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 14   |           | arany |
      | E2E User2 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User1 | arany | 25   |
      | E2E User2 | ezüst | 18   |
      | E2E User3 | bronz | 15   |

  # Korábban a TrophyCalculator egy fantom (user nélküli) 0 pontos bejegyzéssel indult,
  # így a bronz mellé egy gazdátlan kupa is keletkezett.
  Forgatókönyv: Aki nem tippelt, 0 pontos, és nem keletkezik gazdátlan kupa
    Amennyiben lezajlott hétvége van eredményekkel, de E2E User3 nem tippelt
    És "E2E User1" belép
    Akkor a hétvégén pontosan 3 kupát osztottak ki
    # 0 gyűjtött pont -> 0 bajnoki pont; a bronz (a jelenlegi szabály szerint) a 0 pontos harmadiké
    És a VB tabella:
      | név       | pont | kupa  |
      | E2E User1 | 14   | arany |
      | E2E User2 | 10   | ezüst |
      | E2E User3 | 0    | bronz |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  |
      | E2E User3 | bronz |
