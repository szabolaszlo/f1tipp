# language: hu
@sprint
Jellemző: Sprint hétvége (3 esemény: sprint, időmérő, futam)
  Egy sprintes hétvégén három eseményre kell külön-külön tippelni.
  Pontozás (src/Rule/RuleType):
    - Sprint Futam: 1–8. hely, pontos hely 1 pont, rossz helyen 0 – nincs "bent van, de rossz helyen" pont
    - Időmérő: 1–10. hely, pontos hely 4 pont, top 10-ben de rossz helyen 1 pont
    - Futam: 1–10. hely, pontos hely 3 pont, top 10-ben de rossz helyen 1 pont, eltalált safety car 3 pont
  A hétvégi pont a három esemény összege. A hétvégi sorrend adja a bajnoki pontot (14, 10, 8, …)
  és a kupákat (arany, ezüst, bronz; a Trófeák oldalon 25, 18, 15 pont).
  A 2 eseményes hétvégét (időmérő + futam) a specs/ alatti tesztek fedik.

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
    És elküldi a tippet
    Akkor a tipp rögzítve
    Ha az "Időmérő" fülre vált
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
    Akkor a "SPRINT FUTAM" táblán csak a tippelők neve látszik: "Tippesek: E2E User1"
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
    És a "SPRINT FUTAM" táblán "E2E User1" tippjében csak az 5, 6, 7, 8. hely pontos, a többi jelöletlen
    És a "SPRINT FUTAM" táblán "E2E User3" tippjében egyik hely sem pontos, a többi jelöletlen
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
      | E2E User2 | 75           | 14           |
      | E2E User1 | 74           | 10           |
      | E2E User3 | 15           | 8            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 14   |           | arany |
      | E2E User1 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
    És az "Időmérők Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 40   |
      | E2E User2 | 34   |
      | E2E User3 | 6    |
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 75   |
      | E2E User1 | 74   |
      | E2E User3 | 15   |
    És az utolsó hétvége dobogója:
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
      | E2E User1 | 78           | 14           |
      | E2E User2 | 71           | 10           |
      | E2E User3 | 15           | 8            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 14   |           | arany |
      | E2E User2 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
    És a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 78   |
      | E2E User2 | 71   |
      | E2E User3 | 15   |
    És az utolsó hétvége dobogója:
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
      | E2E User2 | 75           | 14           |
      | E2E User1 | 74           | 10           |
      | E2E User3 | 15           | 8            |

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
      | E2E User2 | 81           | 14           |
      | E2E User1 | 68           | 10           |
      | E2E User3 | 15           | 8            |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 14   |           | arany |
      | E2E User1 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
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
      | E2E User1 | 70           | 14           |
      | E2E User2 | 67           | 10           |
      | E2E User3 | 15           | 8            |
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
