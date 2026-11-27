# language: hu
@season-reset
Jellemző: Szezonváltás admin oldal
  Az admin "Karbantartás / Szezonváltás" oldala (ROLE_SUPER_ADMIN): megmutatja, mi törlődik, és csak akkor ad
  megerősítő kódot és gombot, ha a nullázás engedélyezett. A tiltások: nincs mit nullázni, hiányzik az archív
  Információ-bejegyzés, a lezárandó szezon évében még nem nullázható, jövőbeli eseményhez tartozik eredmény.
  A seed: src/E2e/E2eScenarios.php (previous-season*, current-season). Y = tavalyi év.

  Forgatókönyv: A vendég nem éri el az oldalt
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    Ha vendégként megnyitja a Szezonváltás oldalt
    Akkor nem látszik a Szezonváltás oldal

  Forgatókönyv: Az admin nem látja a menüpontot, az oldal és a nullázás neki tiltott
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Admin" belép
    És megjegyzi az adatbázis állapotát
    Ha megnyitja az admin eredmény listát
    Akkor a Karbantartás menüben nincs Szezonváltás
    Ha a böngészőből lekéri a Szezonváltás oldalt
    Akkor a szerver 403 státusszal elutasítja
    Ha a böngészőből közvetlenül elküldi a szezon nullázását
    Akkor a szerver 403 státusszal elutasítja
    És az adatbázis nem változott

  Forgatókönyv: A szuperadmin a menüből eléri az oldalt
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja az admin eredmény listát
    És a Karbantartás menüben a Szezonváltásra kattint
    Akkor megnyílik a Szezonváltás oldal

  Forgatókönyv: Az oldal a törlődő sorok számát az adatbázisból mutatja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a törlődő darabszámok megegyeznek az adatbázis sorszámaival
    És látszik, hogy mi marad meg

  Forgatókönyv: Archív Információ nélkül tiltott a nullázás, felvétele után engedélyezett
    Amennyiben a tavalyi szezon lezárult, de nincs archív bejegyzés
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a tiltások között látszik a tavalyi évre: "Hiányzik a „{Y}” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva."
    És nincs nullázás gomb
    Ha a szuperadmin felvesz egy új információs oldalt:
      | cím      | E2E archív    |
      | slug     | {tavalyi év}  |
      | tartalom | <p>archív</p> |
    És megnyitja a Szezonváltás oldalt
    Akkor van nullázás gomb

  Forgatókönyv: A szezon évében még tiltott a nullázás
    Amennyiben idei eredmény van egy lezajlott eseményen
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a tiltások között látszik az idei évre: "A {Y}. évi szezon {Y+1}. január 1-jétől nullázható."
    És nincs nullázás gomb

  Forgatókönyv: Jövőbe tett eseményhez tartozó eredmény tiltja a nullázást
    Amennyiben a tavalyi szezon lezárult, de egy eredményes eseményt már jövőbe tettek
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a tiltások között látszik: "Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át."
    És nincs nullázás gomb

  Forgatókönyv: Ha nincs mit nullázni, nincs gomb
    Amennyiben nyitott hétvége van
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a tiltások között látszik: "Nincs mit nullázni."
    És nincs nullázás gomb

  Forgatókönyv: Tippek eredmény nélkül: nincs tiltás, van gomb és megerősítő kód
    Amennyiben mindhárom játékos tippelt, de még nincs eredmény
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor nincs tiltás
    És van nullázás gomb
    És látszik a 6 jegyű megerősítő kód

  Forgatókönyv: A jövőbeli eseményre leadott tippekre figyelmeztet, de nem tiltja a nullázást
    Amennyiben E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor látszik a figyelmeztetés: "2 tipp jövőbeli eseményre szól, ezek is törlődnek."
    És van nullázás gomb
