# language: hu
@season-reset
Jellemző: Szezonváltás admin oldal
  Az admin "Karbantartás / Szezonváltás" oldala (ROLE_SUPER_ADMIN): megmutatja, mi törlődik, és csak akkor ad
  megerősítő kódot és gombot, ha a nullázás engedélyezett. A tiltások: nincs mit nullázni, hiányzik az archív
  Információ-bejegyzés, a lezárandó szezon évében még nem nullázható, jövőbeli eseményhez tartozik eredmény.
  A nullázás POST-ja a kódot mindig elsőként váltja be (egyszer használható), utána CSRF, majd a tiltások
  újraellenőrzése; minden elutasításnál az adatbázis változatlan.
  A seed: src/E2e/E2eScenarios.php (previous-season*, current-season). Y = tavalyi év.

  # az alkalmazásnak nincs belépési pontja (security.yaml), ezért a vendég 401-et kap, nem a belépés oldalra kerül
  Forgatókönyv: A vendég nem éri el az oldalt és nem nullázhat
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És megjegyzi az adatbázis állapotát
    Ha vendégként megnyitja a Szezonváltás oldalt
    Akkor nem látszik a Szezonváltás oldal
    Ha a böngészőből lekéri a Szezonváltás oldalt
    Akkor a szerver 401 státusszal elutasítja
    Ha a böngészőből közvetlenül elküldi a szezon nullázását
    Akkor a szerver 401 státusszal elutasítja
    És az adatbázis nem változott

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

  Forgatókönyv: Ha a megnyitás óta tiltottá vált, a szerver a jó kóddal és tokennel is elutasítja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    Ha a szuperadmin törli a tavalyi archív Információt
    És megjegyzi az adatbázis állapotát
    És a böngészőből elküldi a megjegyzett kódot
    Akkor megjelenik a hibaüzenet: "A nullázás most nem engedélyezett, semmi nem törlődött."
    És az adatbázis nem változott
    # az elutasított POST is beváltotta a kódot: az archív visszavétele után sem használható
    Ha a szuperadmin felvesz egy új információs oldalt:
      | cím      | E2E archív    |
      | slug     | {tavalyi év}  |
      | tartalom | <p>archív</p> |
    És megjegyzi az adatbázis állapotát
    És a böngészőből elküldi a megjegyzett kódot
    Akkor megjelenik a hibaüzenet: "Hibás vagy lejárt megerősítő kód, semmi nem törlődött."
    És az adatbázis nem változott

  # --- a megerősítő kód

  Forgatókönyv: Rossz kóddal semmi nem törlődik, és új kód jön
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    És megjegyzi az adatbázis állapotát
    Ha rossz kódot ír be, és a Szezon nullázása gombra kattint
    Akkor megjelenik a hibaüzenet: "Hibás vagy lejárt megerősítő kód, semmi nem törlődött."
    És az oldalon új, eltérő megerősítő kód van
    És az adatbázis nem változott

  Forgatókönyv: Üres kóddal semmi nem törlődik
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi az adatbázis állapotát
    Ha a böngészőből üres kóddal elküldi a szezon nullázását
    Akkor megjelenik a hibaüzenet: "Hibás vagy lejárt megerősítő kód, semmi nem törlődött."
    És az adatbázis nem változott

  Forgatókönyv: Az újratöltés előtti, elavult kóddal semmi nem törlődik
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    És újratölti az oldalt
    És megjegyzi az adatbázis állapotát
    Ha a böngészőből elküldi a megjegyzett kódot
    Akkor megjelenik a hibaüzenet: "Hibás vagy lejárt megerősítő kód, semmi nem törlődött."
    És az adatbázis nem változott

  Forgatókönyv: A kód egyszer használható: a második beküldés a közben leadott tippet nem törli
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    Ha beírja a megerősítő kódot, és a Szezon nullázása gombra kattint
    Akkor megjelenik a siker-üzenet
    Ha egy másik böngészőben "E2E User1" leadja az időmérő tippjét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És megjegyzi az adatbázis állapotát
    És a böngészőből elküldi a megjegyzett kódot
    Akkor megjelenik a hibaüzenet: "Hibás vagy lejárt megerősítő kód, semmi nem törlődött."
    És az adatbázis nem változott
    És az adatbázisban 1 tipp van

  Forgatókönyv: Hamis CSRF-tokennel a jó kód sem töröl
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    És megjegyzi az adatbázis állapotát
    Ha a böngészőből hamis tokennel elküldi a megjegyzett kódot
    Akkor megjelenik a hibaüzenet: "Érvénytelen kérés, semmi nem törlődött."
    És az adatbázis nem változott

  # a kódot a CSRF-ellenőrzés előtt váltja be: a hamis tokenes kérés után a jó tokennel sem használható
  Forgatókönyv: A hamis tokenes kérés is beváltja a kódot
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    És megjegyzi az adatbázis állapotát
    Ha a böngészőből a megjegyzett kódot előbb hamis, majd a jó tokennel küldi el
    Akkor megjelenik a hibaüzenet: "Érvénytelen kérés, semmi nem törlődött."
    És megjelenik a hibaüzenet: "Hibás vagy lejárt megerősítő kód, semmi nem törlődött."
    És az adatbázis nem változott

  # --- a sikeres nullázás

  Forgatókönyv: A jó kóddal a szezon nullázódik, minden más megmarad
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi az adatbázis állapotát
    Ha beírja a megerősítő kódot, és a Szezon nullázása gombra kattint
    Akkor megjelenik a siker-üzenet a megjegyzett darabszámokkal
    És a tippek, eredmények, kupák és alternatív pontok törlődtek, a userek pontjai nullázódtak
    És minden más adat változatlan
    Ha megnyitja a Szezonváltás oldalt
    Akkor a tiltások között látszik: "Nincs mit nullázni."
    És nincs nullázás gomb

  Forgatókönyv: A nullázás után a front azonnal az üres szezont mutatja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És a szuperadmin nullázza a szezont
    Ha "E2E User1" belép
    Akkor a VB tabella, sorrendtől függetlenül:
      | név       | pont | kupa |
      | E2E User1 | 0    |      |
      | E2E User2 | 0    |      |
      | E2E User3 | 0    |      |
    És a Trófeák oldal üres
    Ha megnyitja az Eredmények oldalt
    És az Eredmények oldalon a tavalyi évre kattint
    Akkor az oldalon látszik a tavalyi évre: "E2E archív {Y}"
    És nem volt szerverhiba
    És nincs böngészőhiba
