# language: hu
@login
Jellemző: Belépés
  Belépés a felhasználónév + jelszó űrlappal, kilépés, és a session megmaradása újratöltés után.

  Háttér:
    Amennyiben nyitott hétvége van

  Forgatókönyv: Helyes adatokkal be lehet lépni
    Ha a belépés oldalon "e2e_user" felhasználónévvel és "e2e-pass" jelszóval belép
    Akkor a főoldalra kerül
    És a fejlécben "e2e_user" felhasználónév látszik

  Forgatókönyv: Rossz jelszóval nem lehet belépni
    Ha a belépés oldalon "e2e_user" felhasználónévvel és "rossz-jelszo" jelszóval belép
    Akkor a belépésnél hibaüzenet jelenik meg
    És nincs belépve senki

  Forgatókönyv: Ki lehet lépni
    Amennyiben "E2E User1" belép
    Akkor a fejlécben "e2e_user" felhasználónév látszik
    Ha kilép
    Akkor nincs belépve senki

  Forgatókönyv: Vendég nem tippelhet
    Ha vendégként megnyitja a Tippelek oldalt
    Akkor a vendég nem tippelhet: "Nem vagy bejelentkezve!"

  Forgatókönyv: Újratöltés után is belépve marad
    Ha a belépés oldalon "e2e_user" felhasználónévvel és "e2e-pass" jelszóval belép
    Akkor a fejlécben "e2e_user" felhasználónév látszik
    Ha újratölti az oldalt
    Akkor a fejlécben "e2e_user" felhasználónév látszik
