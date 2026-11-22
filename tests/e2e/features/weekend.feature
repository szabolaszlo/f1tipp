# language: hu
@weekend
Jellemző: Hagyományos hétvége (2 esemény: időmérő, futam)
  Egy sprint nélküli hétvégén két eseményre kell külön-külön tippelni. A sprintes, 3 eseményes hétvége
  ugyanezeket a forgatókönyveket a sprint_weekend.feature-ben futja végig.
  Pontozás (src/Rule/RuleType):
    - Időmérő: 1–10. hely, pontos hely 4 pont, top 10-ben de rossz helyen 1 pont
    - Futam: 1–10. hely, pontos hely 3 pont, top 10-ben de rossz helyen 1 pont, eltalált safety car 3 pont
  A hétvégi pont a két esemény összege. A hétvégi sorrend adja a bajnoki pontot (15, 11, 9, …)
  és a kupákat (arany, ezüst, bronz; a Trófeák oldalon 25, 18, 15 pont).

  Forgatókönyv: A hétvége két eseménye időrendben jelenik meg
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Akkor az Aktuális oldal táblái sorban: "IDŐMÉRŐ, FUTAM"
    És a Tippelek oldal fülei sorban: "Időmérő, Futam"
    És az "Időmérő" fül van kiválasztva

  Forgatókönyv: Az időmérő tipp 10 helyezés, a futam tipp 10 helyezés és a safety car, külön-külön kell leadni
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    Akkor még 10 helyezés hiányzik
    És nincs safety car kérdés
    Ha kitölti a helyezéseket: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a tippküldés gomb aktív
    Ha elküldi a tippet
    Akkor a tipp rögzítve
    Ha a "Maradok" gombra kattint
    És a "Futam" fülre vált
    Akkor még 11 helyezés hiányzik
    Ha az "Időmérő" fülre vált
    Akkor a tipp zárolva van, nem törölhető: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"

  Forgatókönyv: Teljes hétvége: tippek, eredmények eseményenként, kupák, majd az időmérő eredmény javítása
    Három játékos tippel a felületen, az admin időrendben rögzíti az eredményeket.
    User1 nyer (70 > 67), az időmérő eredmény javítása után User2 (73 > 64).

    Amennyiben nyitott hétvége van
    Ha "E2E User1" leadja a tippjeit:
      | esemény | helyezések                              | safety car |
      | Időmérő | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB |            |
      | Futam   | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB | Nem        |
    És "E2E User2" belép
    Akkor az "IDŐMÉRŐ" táblán nincs tipp, csak ez a megjegyzés: "Tippesek: E2E User1"
    Ha "E2E User2" leadja a tippjeit:
      | esemény | helyezések                              | safety car |
      | Időmérő | NOR VER LEC PIA HAM RUS SAI ALO GAS ALB |            |
      | Futam   | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB | Igen       |
    Akkor az "IDŐMÉRŐ" táblán "E2E User1" tippje: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Ha "E2E User3" leadja a tippjeit:
      | esemény | helyezések                              | safety car |
      | Időmérő | TSU STR OCO HUL VER NOR LEC PIA HAM RUS |            |
      | Futam   | TSU STR OCO HUL VER NOR LEC PIA HAM RUS | Igen       |
    Akkor minden eseménytáblán látszik a tippje: "E2E User1, E2E User2, E2E User3"

    # Az admin "új eredmény" űrlapja mindig a következő eredmény nélküli eseményt adja (időrendben).
    Ha az admin rögzíti az "Időmérő" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális oldalon:
      | név       | IDŐMÉRŐ |
      | E2E User1 | 40      |
      | E2E User2 | 34      |
      | E2E User3 | 6       |
    És az "IDŐMÉRŐ" táblán "E2E User2" tippjének jelölése: "közeli, közeli, pontos, pontos, pontos, pontos, pontos, pontos, pontos, pontos"
    És az "IDŐMÉRŐ" táblán "E2E User3" tippjének jelölése: "nincs, nincs, nincs, nincs, közeli, közeli, közeli, közeli, közeli, közeli"
    És még nincs hétvégi összesítő és kupa

    Ha az admin rögzíti a "Futam" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB", safety car: "Igen"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 40      | 30    |
      | E2E User2 | 34      | 33    |
      | E2E User3 | 6       | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 15           |
      | E2E User2 | 67           | 11           |
      | E2E User3 | 15           | 9            |
    # az admin nem tagja a bajnokságnak: nincs a tabellában
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 15   |           | arany |
      | E2E User2 | 11   | + 4       | ezüst |
      | E2E User3 | 9    | + 6       | bronz |
    És az "Időmérők Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 40   |
      | E2E User2 | 34   |
      | E2E User3 | 6    |
    És a "Futamok Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 33   |
      | E2E User1 | 30   |
      | E2E User3 | 9    |
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 70   |
      | E2E User2 | 67   |
      | E2E User3 | 15   |
    És a Kupák modulban az "E2E Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User1 | 70   |
      | ezüst | E2E User2 | 67   |
      | bronz | E2E User3 | 15   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User1 | arany | 25   |
      | E2E User2 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

    # a javított időmérő eredményt E2E User2 tippelte pontosan
    Ha az admin az "Időmérő" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 34      | 30    |
      | E2E User2 | 40      | 33    |
      | E2E User3 | 6       | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 73           | 15           |
      | E2E User1 | 64           | 11           |
      | E2E User3 | 15           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 15   |           | arany |
      | E2E User1 | 11   | + 4       | ezüst |
      | E2E User3 | 9    | + 6       | bronz |
    És az "Időmérők Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 40   |
      | E2E User1 | 34   |
      | E2E User3 | 6    |
    # korábban hibás volt: a 'topWeekendUsers' cache-t semmi nem ürítette, javítás után a régi pontok maradtak
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 73   |
      | E2E User1 | 64   |
      | E2E User3 | 15   |
    És a Kupák modulban az "E2E Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User2 | 73   |
      | ezüst | E2E User1 | 64   |
      | bronz | E2E User3 | 15   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User2 | arany | 25   |
      | E2E User1 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

  # A lezajlott hétvége (src/E2e/E2eScenarios.php: results-entered, BETS) tippjei és eredményei
  # ugyanazok, mint a fenti teljes forgatókönyvben a javítás előtt.

  Forgatókönyv: Az eredmények után elöl a hétvégi összesítő, utána az események, a legközelebbi elöl
    Amennyiben lezajlott hétvége van eredményekkel
    Akkor az Aktuális oldal táblái sorban: "HÉTVÉGE, FUTAM, IDŐMÉRŐ"
    És a hétvégi összesítő az Aktuális oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 15           |
      | E2E User2 | 67           | 11           |
      | E2E User3 | 15           | 9            |

  # Futam javítva: NOR VER … + safety car Nem. Futam: User1 8×3 + 2 + 3 (SC) = 29, User2 26, User3 6.
  Forgatókönyv: A futam eredmény és a safety car javítása után az időmérő pontjai megmaradnak, a hétvége újraszámolódik
    Amennyiben lezajlott hétvége van eredményekkel
    Ha az admin a "Futam" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB", safety car: "Nem"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 40      | 29    |
      | E2E User2 | 34      | 26    |
      | E2E User3 | 6       | 6     |
    És a "FUTAM" táblán "E2E User1" tippjének jelölése: "közeli, közeli, pontos, pontos, pontos, pontos, pontos, pontos, pontos, pontos, pontos"
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 69           | 15           |
      | E2E User2 | 60           | 11           |
      | E2E User3 | 12           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 15   |           | arany |
      | E2E User2 | 11   | + 4       | ezüst |
      | E2E User3 | 9    | + 6       | bronz |
    És az "Időmérők Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 40   |
      | E2E User2 | 34   |
      | E2E User3 | 6    |
    És a "Futamok Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 29   |
      | E2E User2 | 26   |
      | E2E User3 | 6    |
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 69   |
      | E2E User2 | 60   |
      | E2E User3 | 12   |
    És a Kupák modulban az "E2E Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User1 | 69   |
      | ezüst | E2E User2 | 60   |
      | bronz | E2E User3 | 12   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User1 | arany | 25   |
      | E2E User2 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

  # futam: User1 30, User2 33, User3 9 -> bajnoki pont User2 15, User1 11, User3 9
  Forgatókönyv: Az időmérő eredmény törlése után a hétvége csak a futamból áll
    Amennyiben lezajlott hétvége van eredményekkel
    Ha az admin törli az "Időmérő" eredményét
    # nincs eredmény, de a határidő lejárt: a tippek látszanak, pont és pontos / közeli jelölés nélkül
    Akkor az "IDŐMÉRŐ" táblán "E2E User1" tippje pont és jelölés nélkül látszik
    És a pontok az Aktuális oldalon:
      | név       | FUTAM |
      | E2E User1 | 30    |
      | E2E User2 | 33    |
      | E2E User3 | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 33           | 15           |
      | E2E User1 | 30           | 11           |
      | E2E User3 | 9            | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 15   |           | arany |
      | E2E User1 | 11   | + 4       | ezüst |
      | E2E User3 | 9    | + 6       | bronz |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User2 | arany | 25   |
      | E2E User1 | ezüst | 18   |
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
      | E2E User1 | 15   | arany |
      | E2E User2 | 11   | ezüst |
      | E2E User3 | 0    | bronz |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  |
      | E2E User3 | bronz |
