# language: hu
@pages
Jellemző: Üzenőfal és oldalak
  A menüs oldalak hiba nélkül betöltődnek; statisztika, online jelzés, hírek és a csak tagoknak szóló modulok.

  Háttér:
    Amennyiben lezajlott hétvége van eredményekkel

  Forgatókönyv: A belépett user írhat az üzenőfalra
    Amennyiben "E2E User1" belép
    Ha üzenetet ír az üzenőfalra: "Hajrá E2E!"
    Akkor megjelenik az üzenőfalon: "Hajrá E2E!"

  Forgatókönyv: A Statisztika oldal hiba nélkül betöltődik
    Amennyiben "E2E User1" belép
    Akkor a "/#/statistics" oldal hiba nélkül betöltődik

  Forgatókönyv: A Naptár oldal hiba nélkül betöltődik
    Amennyiben "E2E User1" belép
    Akkor a "/#/calendar" oldal hiba nélkül betöltődik

  Forgatókönyv: A Szabályok oldal hiba nélkül betöltődik
    Amennyiben "E2E User1" belép
    Akkor a "/#/rules" oldal hiba nélkül betöltődik

  Forgatókönyv: A Trófeák oldal hiba nélkül betöltődik
    Amennyiben "E2E User1" belép
    Akkor a "/#/trophies" oldal hiba nélkül betöltődik

  Forgatókönyv: Az Eredmények oldal hiba nélkül betöltődik
    Amennyiben "E2E User1" belép
    Akkor a "/#/results" oldal hiba nélkül betöltődik

  Forgatókönyv: A Szabályok oldal a szabályok információs oldalt mutatja
    Ha megnyitja a Szabályok oldalt
    Akkor az oldalon látszik: "E2E szabályok"

  # pozíciónként hány %-ban tippelték / lett az adott pilóta;
  # időmérő 1. hely tippek: VER (User1), NOR (User2), TSU (User3); eredmény: VER
  Forgatókönyv: A statisztika a tippek és az eredmények megoszlását mutatja
    Amennyiben "E2E User1" belép
    Ha megnyitja a Statisztika oldalt
    Akkor az "Időmérő Tippek Statisztikája" panel "1." sora:
      | VER | 33.33% |
      | NOR | 33.33% |
      | TSU | 33.33% |
    És az "Időmérő Eredmények Statisztikája" panel "1." sora:
      | VER | 100% |

  # online jelzés: zöld pötty a belépett user neve mellett a VB tabellában (assets/js/app.js, /get_online_user)
  Forgatókönyv: A belépett user online-ként látszik a tabellában
    Amennyiben "E2E User1" belép
    Akkor a VB tabellában csak "E2E User1" van online jelöléssel

  Forgatókönyv: A vendég látja a híreket, a tabellát és a kupákat, de a tagoknak szóló modulokat nem
    Ha megnyitja a főoldalt
    Akkor látszik a hír: "E2E hír: lezárult a szezon előtti teszt"
    És az oldalsávban látszik a VB tabella, benne: "E2E User1"
    És nem látszanak a csak tagoknak szóló modulok
    És nincs böngészőhiba

  Forgatókönyv: A belépett user látja a tagoknak szóló modulokat is
    Amennyiben "E2E User1" belép
    Akkor látszik a hír: "E2E hír: lezárult a szezon előtti teszt"
    És látszanak a csak tagoknak szóló modulok
    És nincs böngészőhiba
