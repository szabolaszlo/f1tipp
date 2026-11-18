# language: hu
@order
Jellemző: Hétvégi sorrend
  Az aktuális hétvége eseményei mindig a "most"-hoz legközelebbi elöl: ha a futam már közelebb van, mint
  a (lezajlott) időmérő, a futam kerül előre – az Aktuális oldalon és a Tippelek fülein is.
  Eredmény után a hétvégi összesítő áll legelöl. A következő hétvégére a futam után 3 nappal vált az oldal.

  Forgatókönyv: Az időmérő előtt a legközelebbi esemény van elöl
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Akkor az Aktuális oldal táblái sorban: "IDŐMÉRŐ, FUTAM"
    És a Tippelek oldal fülei sorban: "Időmérő, Futam"
    És az "Időmérő" fül van kiválasztva

  Forgatókönyv: Ha a futam közelebb van, mint a lezajlott időmérő, a futam van elöl
    Amennyiben vasárnap délelőtt van: az időmérő 20 órája volt, a futam 2 óra múlva lesz
    És "E2E User1" belép
    Akkor az Aktuális oldal táblái sorban: "FUTAM, IDŐMÉRŐ"
    És a Tippelek oldal fülei sorban: "Futam, Időmérő"
    # a nyitott futam fül az alapértelmezett, kitölthető űrlappal
    És a "Futam" fül van kiválasztva
    És van tippelő űrlap

  Forgatókönyv: A futam eredménye után a hétvégi összesítő van elöl
    Amennyiben lezajlott hétvége van eredményekkel
    Akkor az Aktuális oldal táblái sorban: "HÉTVÉGE, FUTAM, IDŐMÉRŐ"

  Forgatókönyv: Két nappal a futam után még a lezajlott hétvége látszik
    Amennyiben a hétvége futama 2 napja volt
    Akkor az Aktuális oldal címe: "E2E Nagydíj"
    És az Aktuális oldal táblái sorban: "HÉTVÉGE, FUTAM, IDŐMÉRŐ"

  Forgatókönyv: Több mint három nappal a futam után a következő hétvége látszik
    Amennyiben a hétvége futama 4 napja volt
    És "E2E User1" belép
    Akkor az Aktuális oldal címe: "E2E Második Nagydíj"
    És az Aktuális oldal táblái sorban: "IDŐMÉRŐ, FUTAM"
    És a Tippelek oldal fülei sorban: "Időmérő, Futam"
    És van tippelő űrlap
