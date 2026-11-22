# language: hu
@display
Jellemző: Mentett tipp megjelenítése
  A rögzített tipp zárolva van (nem módosítható), és megjelenik az Aktuális oldalon.

  Háttér:
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    És a Tippelek oldalon az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És elküldi a tippet
    És a tipp rögzítve
    És a "Maradok" gombra kattint

  Forgatókönyv: Újranyitáskor a mentett tipp zárolva van
    Ha újratölti az oldalt
    És az "Időmérő" fülre vált
    Akkor a tipp zárolva van, nem törölhető: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És az 1. hely választója le van tiltva

  Forgatókönyv: A mentett tipp megjelenik az Aktuális oldalon
    Akkor az "IDŐMÉRŐ" táblán "E2E User1" tippje: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
