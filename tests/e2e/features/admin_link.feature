# language: hu
@admin-link
Jellemző: Admin gomb a fejlécben
  A fejléc "Admin" gombja (assets/js/components/Navigation.vue) csak ROLE_ADMIN (és a hierarchia miatt
  ROLE_SUPER_ADMIN) jogú felhasználónak jelenik meg, és az EasyAdmin felületre visz.

  Háttér:
    Amennyiben nyitott hétvége van

  Forgatókönyv: Vendég nem látja az Admin gombot
    Ha megnyitja a főoldalt
    Akkor nincs belépve senki
    És nincs Admin gomb a fejlécben

  Forgatókönyv: Sima felhasználó nem látja az Admin gombot
    Amennyiben "E2E User1" belép
    Akkor a fejlécben "e2e_user" felhasználónév látszik
    És nincs Admin gomb a fejlécben

  Forgatókönyv: Az admin látja az Admin gombot, és az az admin felületre visz
    Amennyiben "E2E Admin" belép
    Akkor a fejlécben "e2e_admin" felhasználónév látszik
    Ha az Admin gombra kattint
    Akkor megnyílik az admin felület
    És megjelenik: "F1Tipp Admin"

  Forgatókönyv: A szuperadmin látja az Admin gombot, és az az admin felületre visz
    Amennyiben "E2E Superadmin" belép
    Akkor a fejlécben "e2e_superadmin" felhasználónév látszik
    Ha az Admin gombra kattint
    Akkor megnyílik az admin felület

  Forgatókönyv: Az admin kilépése után eltűnik az Admin gomb
    Amennyiben "E2E Admin" belép
    Akkor van Admin gomb a fejlécben
    Ha kilép
    Akkor nincs belépve senki
    És nincs Admin gomb a fejlécben
