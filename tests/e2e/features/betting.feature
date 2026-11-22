# language: hu
@betting
Jellemző: Tippleadás
  A Tippelek oldalon (assets/js/components/Betting.vue) a hétvége eseményei fülek. Az időmérőre 10 helyezést,
  a futamra 10 helyezést és a safety car kérdést kell kitölteni; egy pilóta csak egy helyen szerepelhet.
  2 eseményes hétvége (időmérő + futam); a sprintes hétvégét a sprint_weekend.feature fedi.

  Háttér:
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    És megnyitja a Tippelek oldalt

  Forgatókönyv: A hétvége eseményei időrendben, fülekként jelennek meg
    Akkor a fülek sorban: "Időmérő, Futam"

  Forgatókönyv: Amíg nincs minden helyezés kitöltve, nem lehet elküldeni
    Ha az "Időmérő" fülre vált
    Akkor még 10 helyezés hiányzik
    És a tippküldés gomb le van tiltva
    Ha kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS"
    Akkor még 1 helyezés hiányzik
    És a tippküldés gomb le van tiltva

  Forgatókönyv: A már kiválasztott pilóta nem szerepel a többi hely választójában
    Ha az "Időmérő" fülre vált
    És az 1. helyre kiválasztja: "VER"
    És megnyitja a 2. hely választóját
    Akkor a választóban nincs "VER"
    És a választóban ott van "NOR"

  Forgatókönyv: A választás törlése felszabadítja a pilótát a többi helyre
    Ha az "Időmérő" fülre vált
    Akkor nincs törlés gomb
    Ha az 1. helyre kiválasztja: "VER"
    Akkor még 9 helyezés hiányzik
    Ha törli az 1. hely választását
    Akkor még 10 helyezés hiányzik
    És nincs törlés gomb
    Ha megnyitja a 2. hely választóját
    Akkor a választóban ott van "VER"

  Forgatókönyv: Elküldés előtt két pilóta helyet cserélhet
    Ha az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a tippküldés gomb aktív
    Ha törli a 2. hely választását
    Akkor a tippküldés gomb le van tiltva
    Ha az 1. helyre kiválasztja: "NOR"
    És a 2. helyre kiválasztja: "VER"
    Akkor az 1. helyen "NOR" van kiválasztva
    És a 2. helyen "VER" van kiválasztva
    És a tippküldés gomb aktív

  Forgatókönyv: Backspace a választón törli a választást
    Ha az "Időmérő" fülre vált
    És az 1. helyre kiválasztja: "VER"
    És az 1. hely választóján Backspace-t nyom
    Akkor az 1. hely üres

  Forgatókönyv: Fülváltás után a be nem küldött választások megmaradnak
    Ha az "Időmérő" fülre vált
    És az 1. helyre kiválasztja: "VER"
    És a "Futam" fülre vált
    Akkor az 1. hely üres
    Ha az "Időmérő" fülre vált
    Akkor az 1. helyen "VER" van kiválasztva
    És még 9 helyezés hiányzik

  Forgatókönyv: A futamhoz a safety car kérdésre is válaszolni kell
    Ha a "Futam" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor még 1 helyezés hiányzik
    És a tippküldés gomb le van tiltva
    Ha a safety car kérdésre ezt válaszolja: "Igen"
    Akkor a tippküldés gomb aktív

  Forgatókönyv: Mentés után átirányít az Aktuális oldalra
    Ha az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És elküldi a tippet
    Akkor a tipp rögzítve
    És átirányít az Aktuális oldalra

  Forgatókönyv: A Maradok gomb megállítja az átirányítást
    Ha az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És elküldi a tippet
    Akkor a tipp rögzítve
    Ha a "Maradok" gombra kattint
    Akkor 4 másodperc múlva is a Tippelek oldalon marad

  Forgatókönyv: A hétvége eseményeire külön-külön kell tippelni
    Ha az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És elküldi a tippet
    Akkor a tipp rögzítve
    Ha a "Maradok" gombra kattint
    És a "Futam" fülre vált
    Akkor még 11 helyezés hiányzik
    És az 1. hely üres
    Ha az "Időmérő" fülre vált
    Akkor a tipp zárolva van, nem törölhető: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
