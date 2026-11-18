# language: hu
@security
Jellemző: Jogosultságok és visszaélések
  A tippelő formot a böngészőből, a felhasználó saját sessionjével is beküldjük – mintha valaki a fejlesztői
  eszközökkel a zárolt mezőket megkerülve próbálná. A szerver jogosultság-ellenőrzését közvetlen kérésekkel nézzük.

  Forgatókönyv: A rögzített tipp a form újraküldésével sem írható felül
    Amennyiben E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem
    És "E2E User1" belép
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És elküldi a tippet
    Akkor a tipp rögzítve
    # mentés után már látszanak a többiek tippjei – ezek alapján próbálná átírni a sajátját
    Ha a böngészőből közvetlenül beküldi az "Időmérő" tippet: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a szerver 409 státusszal elutasítja: "A tipped már rögzítve van, nem módosítható."
    És az "IDŐMÉRŐ" táblán "E2E User1" tippje: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"

  Forgatókönyv: A szerver elutasítja, ha ugyanaz a pilóta két helyen szerepel
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Ha a böngészőből közvetlenül beküldi az "Időmérő" tippet: "VER VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a szerver 422 státusszal elutasítja
    És az "IDŐMÉRŐ" táblán nincs "E2E User1" tipp-sora

  Forgatókönyv: Vendég nem küldhet tippet és nem olvashatja az üzenőfalat
    Amennyiben nyitott hétvége van
    Ha megnyitja a főoldalt
    Akkor a kérések válasza:
      | metódus | útvonal                          | státusz |
      | GET     | /module/betting/form/{időmérő}   | 401     |
      | POST    | /module/betting/form/{időmérő}   | 401     |
      | GET     | /module/messages/list            | 401     |
    És nincs üzenet beviteli mező

  Forgatókönyv: Az üzenőfal a felhasználók HTML-jét szövegként mutatja
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Ha üzenetet ír az üzenőfalra: '<img src=x onerror="window.__xss=1"><b>vastag</b> www.example.com'
    Akkor megjelenik az üzenőfalon: "<b>vastag</b>"
    És az üzenőfalon nincs HTML elem: "img, b"
    És az üzenetbe írt szkript nem futott le
    # a linkesítés továbbra is működik
    És az üzenőfalon link mutat ide: "http://www.example.com"
    # újratöltés után (szerveroldali renderelés) is szövegként jelenik meg
    Ha újratölti az oldalt
    Akkor megjelenik az üzenőfalon: "<b>vastag</b>"
    És az üzenőfalon nincs HTML elem: "img"
    És az üzenetbe írt szkript nem futott le

  Forgatókönyv: Vendég nem nyithatja meg az admint
    Amennyiben nyitott hétvége van
    Ha vendégként megnyitja az admin felületet
    Akkor nem látszik az admin felület

  Forgatókönyv: Sima felhasználó nem nyithatja meg az admint és a karbantartó műveleteit
    Amennyiben lezajlott hétvége van eredményekkel
    És "E2E User1" belép
    Akkor a kérések válasza:
      | metódus | útvonal                                | státusz |
      | GET     | /admin                                 | 403     |
      | GET     | /admin/maintenance/re_calculate_points | 403     |
      | GET     | /admin/maintenance/cache_clear         | 403     |

  # az esemény, pilóta és felhasználó menük ROLE_SUPER_ADMIN-hoz kötöttek
  Forgatókönyv: Az admin rögzíthet eredményt, de eseményt, pilótát és felhasználót nem szerkeszthet
    Amennyiben nyitott hétvége van
    És "E2E Admin" belép
    Ha megnyitja az admin eredmény listát
    Akkor a kérések válasza:
      | metódus | útvonal                       | státusz |
      | GET     | admin Event edit {időmérő}    | 403     |
      | GET     | admin Driver edit 1           | 403     |
      | GET     | admin User edit 1             | 403     |
