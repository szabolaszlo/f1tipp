# language: hu
@sprint
Jellemző: Sprint hétvége (3 esemény: sprint, időmérő, futam)
  Egy sprintes hétvégén három eseményre kell külön-külön tippelni.
  Pontozás (src/Rule/RuleType):
    - Sprint Futam: 1–8. hely, pontos hely 1 pont, rossz helyen 0 – nincs "bent van, de rossz helyen" pont
    - Időmérő: 1–10. hely, pontos hely 4 pont, top 10-ben de rossz helyen 1 pont
    - Futam: 1–10. hely, pontos hely 3 pont, top 10-ben de rossz helyen 1 pont, eltalált safety car 3 pont
  A hétvégi pont a három esemény összege. A hétvégi sorrend adja a bajnoki pontot (15, 11, 9, …)
  és a kupákat (arany, ezüst, bronz; a Trófeák oldalon 25, 18, 15 pont).
  A sprint nélküli, 2 eseményes hétvége ugyanezeket a forgatókönyveket a weekend.feature-ben futja végig.

  Forgatókönyv: A hétvége három eseménye időrendben jelenik meg
    Amennyiben nyitott sprint hétvége van
    És "E2E User1" belép
    Akkor az Aktuális oldal táblái sorban: "SPRINT FUTAM, IDŐMÉRŐ, FUTAM"
    És a Tippelek oldal fülei sorban: "Sprint Futam, Időmérő, Futam"
    És a "Sprint Futam" fül van kiválasztva

  Forgatókönyv: A sprint tipp 8 helyezés, és a többi eseménytől külön kell leadni
    Amennyiben nyitott sprint hétvége van
    És "E2E User1" belép
    Ha a Tippelek oldalon a "Sprint Futam" fülre vált
    Akkor még 8 helyezés hiányzik
    És nincs safety car kérdés
    Ha kitölti a helyezéseket: "NOR VER LEC PIA HAM RUS SAI ALO"
    Akkor a tippküldés gomb aktív
    Ha elküldi a tippet
    Akkor a tipp rögzítve
    Ha a "Maradok" gombra kattint
    És az "Időmérő" fülre vált
    Akkor még 10 helyezés hiányzik
    Ha a "Futam" fülre vált
    Akkor még 11 helyezés hiányzik
    Ha a "Sprint Futam" fülre vált
    Akkor a tipp zárolva van, nem törölhető: "NOR VER LEC PIA HAM RUS SAI ALO"

  Forgatókönyv: Teljes sprint hétvége: tippek, eredmények eseményenként, kupák, majd a sprint eredmény javítása
    Három játékos tippel a felületen, az admin időrendben rögzíti az eredményeket.
    Sprint nélkül User1 nyerne (70 > 67), a sprinttel User2 nyer (75 > 74),
    a sprint eredmény javítása után megint User1 (78 > 71).

    Amennyiben nyitott sprint hétvége van
    Ha "E2E User1" leadja a tippjeit:
      | esemény      | helyezések                              | safety car |
      | Sprint Futam | LEC PIA VER NOR HAM RUS SAI ALO         |            |
      | Időmérő      | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB |            |
      | Futam        | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB | Nem        |
    És "E2E User2" belép
    Akkor a "SPRINT FUTAM" táblán nincs tipp, csak ez a megjegyzés: "Tippesek: E2E User1"
    Ha "E2E User2" leadja a tippjeit:
      | esemény      | helyezések                              | safety car |
      | Sprint Futam | NOR VER LEC PIA HAM RUS SAI ALO         |            |
      | Időmérő      | NOR VER LEC PIA HAM RUS SAI ALO GAS ALB |            |
      | Futam        | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB | Igen       |
    Akkor a "SPRINT FUTAM" táblán "E2E User1" tippje: "LEC PIA VER NOR HAM RUS SAI ALO"
    Ha "E2E User3" leadja a tippjeit:
      | esemény      | helyezések                              | safety car |
      | Sprint Futam | TSU STR OCO HUL VER NOR LEC PIA         |            |
      | Időmérő      | TSU STR OCO HUL VER NOR LEC PIA HAM RUS |            |
      | Futam        | TSU STR OCO HUL VER NOR LEC PIA HAM RUS | Igen       |
    Akkor minden eseménytáblán látszik a tippje: "E2E User1, E2E User2, E2E User3"

    Ha az admin rögzíti a "Sprint Futam" eredményét: "NOR VER LEC PIA HAM RUS SAI ALO"
    Akkor a pontok az Aktuális oldalon:
      | név       | SPRINT FUTAM |
      | E2E User1 | 4            |
      | E2E User2 | 8            |
      | E2E User3 | 0            |
    # a sprintben nincs "bent van, de rossz helyen" pont: csak a pontos helyek jelöltek
    És a "SPRINT FUTAM" táblán "E2E User1" tippjének jelölése: "nincs, nincs, nincs, nincs, pontos, pontos, pontos, pontos"
    És a "SPRINT FUTAM" táblán "E2E User3" tippjének jelölése: "nincs, nincs, nincs, nincs, nincs, nincs, nincs, nincs"
    És még nincs hétvégi összesítő és kupa

    Ha az admin rögzíti az "Időmérő" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális oldalon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ |
      | E2E User1 | 4            | 40      |
      | E2E User2 | 8            | 34      |
      | E2E User3 | 0            | 6       |
    És még nincs hétvégi összesítő és kupa

    Ha az admin rögzíti a "Futam" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB", safety car: "Igen"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 4            | 40      | 30    |
      | E2E User2 | 8            | 34      | 33    |
      | E2E User3 | 0            | 6       | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 75           | 15           |
      | E2E User1 | 74           | 11           |
      | E2E User3 | 15           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 15   |           | arany |
      | E2E User1 | 11   | + 4       | ezüst |
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
      | E2E User2 | 75   |
      | E2E User1 | 74   |
      | E2E User3 | 15   |
    És a Kupák modulban az "E2E Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User2 | 75   |
      | ezüst | E2E User1 | 74   |
      | bronz | E2E User3 | 15   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User2 | arany | 25   |
      | E2E User1 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

    Ha az admin a "Sprint Futam" eredményét erre javítja: "LEC PIA VER NOR HAM RUS SAI ALO"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 8            | 40      | 30    |
      | E2E User2 | 4            | 34      | 33    |
      | E2E User3 | 0            | 6       | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 78           | 15           |
      | E2E User2 | 71           | 11           |
      | E2E User3 | 15           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 15   |           | arany |
      | E2E User2 | 11   | + 4       | ezüst |
      | E2E User3 | 9    | + 6       | bronz |
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 78   |
      | E2E User2 | 71   |
      | E2E User3 | 15   |
    És a Kupák modulban az "E2E Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User1 | 78   |
      | ezüst | E2E User2 | 71   |
      | bronz | E2E User3 | 15   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User1 | arany | 25   |
      | E2E User2 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

  # A lezajlott hétvége (src/E2e/E2eScenarios.php: sprint-results-entered) tippjei és eredményei
  # ugyanazok, mint a fenti teljes forgatókönyvben a javítás előtt.

  Forgatókönyv: Az eredmények után elöl a hétvégi összesítő, utána az események, a legközelebbi elöl
    Amennyiben lezajlott sprint hétvége van eredményekkel
    Akkor az Aktuális oldal táblái sorban: "HÉTVÉGE, FUTAM, IDŐMÉRŐ, SPRINT FUTAM"
    És a hétvégi összesítő az Aktuális oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 75           | 15           |
      | E2E User1 | 74           | 11           |
      | E2E User3 | 15           | 9            |

  Forgatókönyv: Az időmérő javítása után a sprint pontjai megmaradnak és beszámítanak a hétvégébe
    Amennyiben lezajlott sprint hétvége van eredményekkel
    Ha az admin az "Időmérő" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 4            | 34      | 30    |
      | E2E User2 | 8            | 40      | 33    |
      | E2E User3 | 0            | 6       | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 81           | 15           |
      | E2E User1 | 68           | 11           |
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
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 81   |
      | E2E User1 | 68   |
      | E2E User3 | 15   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User2 | arany | 25   |
      | E2E User1 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

  # Futam javítva: NOR VER … + safety car Nem. Futam: User1 8×3 + 2 + 3 (SC) = 29, User2 26, User3 6.
  # A javítás előtt User2 nyert (75 > 74), utána User1 (73 > 68).
  Forgatókönyv: A futam eredmény és a safety car javítása után a sprint és az időmérő pontjai megmaradnak, a hétvége újraszámolódik
    Amennyiben lezajlott sprint hétvége van eredményekkel
    Ha az admin a "Futam" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB", safety car: "Nem"
    Akkor a pontok az Aktuális és az Eredmények oldalon:
      | név       | SPRINT FUTAM | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 4            | 40      | 29    |
      | E2E User2 | 8            | 34      | 26    |
      | E2E User3 | 0            | 6       | 6     |
    És a "FUTAM" táblán "E2E User1" tippjének jelölése: "közeli, közeli, pontos, pontos, pontos, pontos, pontos, pontos, pontos, pontos, pontos"
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 73           | 15           |
      | E2E User2 | 68           | 11           |
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
      | E2E User1 | 73   |
      | E2E User2 | 68   |
      | E2E User3 | 12   |
    És a Kupák modulban az "E2E Nagydíj" dobogója:
      | kupa  | név       | pont |
      | arany | E2E User1 | 73   |
      | ezüst | E2E User2 | 68   |
      | bronz | E2E User3 | 12   |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User1 | arany | 25   |
      | E2E User2 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
    És a hétvégén pontosan 3 kupát osztottak ki

  # sprint: User1 4, User2 8, User3 0; futam: User1 30, User2 33, User3 9
  Forgatókönyv: Az időmérő eredmény törlése után a hétvége csak a sprintből és a futamból áll
    Amennyiben lezajlott sprint hétvége van eredményekkel
    Ha az admin törli az "Időmérő" eredményét
    Akkor az "IDŐMÉRŐ" táblán "E2E User1" tippje pont és jelölés nélkül látszik
    És a pontok az Aktuális oldalon:
      | név       | SPRINT FUTAM | FUTAM |
      | E2E User1 | 4            | 30    |
      | E2E User2 | 8            | 33    |
      | E2E User3 | 0            | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User2 | 41           | 15           |
      | E2E User1 | 34           | 11           |
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

  Forgatókönyv: A sprint eredmény törlése után a hétvége csak az időmérőből és a futamból áll
    Sprint nélkül User1 nyer (70 > 67).

    Amennyiben lezajlott sprint hétvége van eredményekkel
    Ha az admin törli a "Sprint Futam" eredményét
    Akkor a "SPRINT FUTAM" táblán "E2E User2" tippje pont és jelölés nélkül látszik
    És a pontok az Aktuális oldalon:
      | név       | IDŐMÉRŐ | FUTAM |
      | E2E User1 | 40      | 30    |
      | E2E User2 | 34      | 33    |
      | E2E User3 | 6       | 9     |
    És a hétvégi összesítő az Aktuális és az Eredmények oldalon:
      | név       | hétvégi pont | bajnoki pont |
      | E2E User1 | 70           | 15           |
      | E2E User2 | 67           | 11           |
      | E2E User3 | 15           | 9            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 15   |           | arany |
      | E2E User2 | 11   | + 4       | ezüst |
      | E2E User3 | 9    | + 6       | bronz |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  | pont |
      | E2E User1 | arany | 25   |
      | E2E User2 | ezüst | 18   |
      | E2E User3 | bronz | 15   |
