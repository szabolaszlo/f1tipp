# language: hu
@admin
Jellemző: Admin menüpontok
  Az admin minden menüpontja: a szerkesztés / létrehozás után a változásnak a "front" oldalon is látszania kell.
  A pilóta / esemény / felhasználó / üzenet / információ / beállítás menük ROLE_SUPER_ADMIN-hoz kötöttek.

  Forgatókönyv: Esemény átnevezése és a határidő áthelyezése látszik a játékosoknak
    Amennyiben nyitott hétvége van
    Ha a szuperadmin megnyitja az adminban az "Időmérő" eseményt
    És a nevét átírja erre: "E2E Átnevezett Nagydíj"
    # a még nyitott időmérő határideje 3 órával korábbra kerül -> már lejárt
    És az időpontját 3 órával korábbra teszi
    És elmenti
    Akkor megjelenik az admin üzenet: "Esemény Cachek törölve"
    Amennyiben "E2E User1" belép
    Akkor az "IDŐMÉRŐ" tábla címe: "E2E Átnevezett Nagydíj"
    # a most lejárt időmérő (1 órája) közelebb van, mint a holnapi futam
    És az Aktuális oldal táblái sorban: "IDŐMÉRŐ, FUTAM"
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    Akkor a tippelési időszak lejárt üzenet látszik
    És nincs tippelő űrlap
    # a naptár alapból csak a hátralévő eseményeket mutatja: a lejárt időmérő csak a szűrő kikapcsolása után látszik
    Ha megnyitja a Naptár oldalt
    Akkor a naptárban látszik: "E2E Nagydíj"
    És a naptárban nem látszik: "E2E Átnevezett Nagydíj"
    Ha a naptárban kikapcsolja a "Csak Hátralévők" szűrőt
    Akkor a naptárban látszik: "E2E Átnevezett Nagydíj"

  # elhalasztott hétvége (pl. időjárás miatt): az eredetileg későbbi, de így előbb sorra kerülő hétvége lesz az aktuális
  Forgatókönyv: Az elhalasztott hétvége helyett a korábbi lesz az aktuális
    Amennyiben nyitott hétvége van
    Ha a szuperadmin a hétvége minden eseményét 14 nappal későbbre teszi
    És "E2E User1" belép
    Akkor az Aktuális oldal címe: "E2E Második Nagydíj"
    És a Tippelek oldal fülei sorban: "Időmérő, Futam"

  Forgatókönyv: Inaktivált, átnevezett és új pilóta a tippelő választóban
    Amennyiben nyitott hétvége van
    Ha a szuperadmin megnyitja az adminban a "VER" pilótát
    És kikapcsolja az aktív jelölést
    És elmenti
    És a szuperadmin megnyitja az adminban a "NOR" pilótát
    És a nevét átírja erre: "Lando Átnevezett"
    És elmenti
    És a szuperadmin felvesz egy új pilótát:
      | név       | Franco Colapinto |
      | rövidítés | COL              |
      | pont      | 0                |
      | aktív     | igen             |
    Akkor az admin listában látszik: "COL"
    Amennyiben "E2E User1" belép
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    És megnyitja az 1. hely választóját
    Akkor a választóban nincs "VER"
    És a választóban ott van "COL"
    És a választóban a "NOR" pilóta neve tartalmazza: "Átnevezett"
    Ha a választóban a "COL" pilótára kattint
    Akkor az 1. helyen "COL" van kiválasztva

  Forgatókönyv: Az adminban létrehozott felhasználó be tud lépni és bekerül a bajnokságba
    Amennyiben lezajlott hétvége van eredményekkel
    Ha a szuperadmin felvesz egy új felhasználót:
      | név             | E2E Újonc    |
      | felhasználónév  | e2e_newbie   |
      | szerepkör       | ROLE_USER    |
      | jelszó          | ujonc-jelszo |
      | bajnokság tagja | igen         |
    Akkor az admin listában látszik: "e2e_newbie"
    És a VB tabellában szerepel: "E2E Újonc"
    Ha kitörli a sütiket
    És a belépés oldalon "e2e_newbie" felhasználónévvel és "ujonc-jelszo" jelszóval belép
    Akkor a főoldalra kerül
    És a fejlécben "e2e_newbie" felhasználónév látszik

  # a jelszó mezőhöz (hash) nem nyúlunk: a régi jelszóval be kell tudnia lépni
  Forgatókönyv: A felhasználó szerkesztése megtartja a jelszót, és kiveheti a bajnokságból
    Amennyiben lezajlott hétvége van eredményekkel
    Ha a szuperadmin megnyitja az adminban az "E2E User3" felhasználót
    És a nevét átírja erre: "E2E User3 Átnevezve"
    És kiveszi a bajnokságból
    És elmenti
    Akkor a VB tabella nevei: "E2E User1, E2E User2"
    Ha kitörli a sütiket
    És a belépés oldalon "e2e_user3" felhasználónévvel és "e2e-pass" jelszóval belép
    Akkor a főoldalra kerül
    És a fejlécben "e2e_user3" felhasználónév látszik

  Forgatókönyv: Szerkesztett és törölt üzenet az üzenőfalon
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Ha üzenetet ír az üzenőfalra: "Eredeti üzenet"
    Akkor megjelenik az üzenőfalon: "Eredeti üzenet"
    Ha a szuperadmin megnyitja az adminban az 1. üzenetet
    És a szövegét átírja erre: "Moderált üzenet"
    És elmenti
    És "E2E User1" belép
    Akkor megjelenik az üzenőfalon: "Moderált üzenet"
    És nem látszik az üzenőfalon: "Eredeti üzenet"
    Ha a szuperadmin törli az 1. üzenetet
    És "E2E User1" belép
    Akkor nem látszik az üzenőfalon: "Moderált üzenet"

  # az Eredmények oldal korábbi évei az információ slug-ja alapján töltődnek (/information_content/{év})
  Forgatókönyv: Szerkesztett szabályok és egy új, korábbi szezon oldal
    Amennyiben nyitott hétvége van
    Ha a szuperadmin megnyitja az adminban a szabályok oldalt
    És a tartalmát átírja erre: "<p>Módosított E2E szabályok</p>"
    És elmenti
    És a szuperadmin felvesz egy új információs oldalt:
      | cím      | 2015-ös szezon                         |
      | slug     | 2015                                   |
      | tartalom | <p>E2E archív eredmények 2015</p>      |
    És "E2E User1" belép
    És megnyitja a Szabályok oldalt
    Akkor az oldalon látszik: "Módosított E2E szabályok"
    Ha megnyitja az Eredmények oldalt
    És az Eredmények oldalon a "2015" évre kattint
    Akkor az oldalon látszik: "E2E archív eredmények 2015"

  Forgatókönyv: A beállított analitika azonosító bekerül az oldalba
    Amennyiben nyitott hétvége van
    Ha a szuperadmin felvesz egy új beállítást: "ga4MeasurementId" = "G-E2ETEST"
    És megnyitja a főoldalt
    Akkor az oldal fejlécében benne van: "gtag('config', 'G-E2ETEST')"

  Forgatókönyv: Karbantartás: cache ürítése
    Amennyiben nyitott hétvége van
    Ha az admin üríti a cache-t
    Akkor megjelenik az admin üzenet: "Sikeres cache törlés"
    És látszik az új eredmény gomb
