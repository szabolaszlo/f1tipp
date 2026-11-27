# language: hu
@season-reset
Jellemző: Szezonváltás admin oldal
  Az admin "Karbantartás / Szezonváltás" oldala (ROLE_SUPER_ADMIN): megmutatja, mi törlődik, és csak akkor ad
  megerősítő kódot és gombot, ha a nullázás engedélyezett. A tiltások: nincs mit nullázni, hiányzik az archív
  Információ-bejegyzés, a lezárandó szezon évében még nem nullázható, jövőbeli eseményhez tartozik eredmény (ilyenkor csak
  ez az ok látszik), nem InnoDB a nullázott táblák valamelyike (ez csak unit tesztben: e2e-ben MyISAM nem állítható elő;
  az e2e a megengedő állapotot, a gombot nézi).
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

  # a jövőbe tett eredményes esemény miatt a lezárandó év megbízhatatlan: az archív- és évszám-tiltás nem látszik
  Forgatókönyv: Jövőbe tett eseményhez tartozó eredmény tiltja a nullázást
    Amennyiben a tavalyi szezon lezárult, de egy eredményes eseményt már jövőbe tettek
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a tiltások között csak ez látszik: "Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át."
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

  Forgatókönyv: Tippek eredmény nélkül: a nullázás a tippeket törli
    Amennyiben mindhárom játékos tippelt, de még nincs eredmény
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi az adatbázis állapotát
    Ha beírja a megerősítő kódot, és a Szezon nullázása gombra kattint
    Akkor megjelenik a siker-üzenet a megjegyzett darabszámokkal
    És az adatbázisban 0 tipp van
    És a tippek, eredmények, kupák és alternatív pontok törlődtek, a userek pontjai nullázódtak
    És nem volt szerverhiba
    És nincs böngészőhiba

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

  # a kód és a token az oldal megnyitásakor érvényes volt; közben egy eredményes eseményt jövőbe tettek
  Forgatókönyv: Ha a megnyitás óta eredményes eseményt tettek jövőbe, a szerver a jó kóddal és tokennel is elutasítja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi a megerősítő kódot
    Ha a szuperadmin az 1. hétvége eseményeit 2 évvel későbbre teszi
    És megjegyzi az adatbázis állapotát
    És a böngészőből elküldi a megjegyzett kódot
    Akkor megjelenik a hibaüzenet: "A nullázás most nem engedélyezett, semmi nem törlődött."
    És az adatbázis nem változott
    És a tiltások között csak ez látszik: "Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át."

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

  # a gomb a beküldéskor letiltódik: a második kattintás nem küld újabb POST-ot (ami a beváltott kód miatt hibaüzenetet adna)
  Forgatókönyv: Dupla kattintásra egyszer nullázódik, csak a siker-üzenet látszik
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    És megnyitja a Szezonváltás oldalt
    És megjegyzi az adatbázis állapotát
    Ha beírja a megerősítő kódot, és duplán kattint a Szezon nullázása gombra
    Akkor egyetlen nullázó kérés ment el
    És megjelenik a siker-üzenet a megjegyzett darabszámokkal
    És a tippek, eredmények, kupák és alternatív pontok törlődtek, a userek pontjai nullázódtak

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
    Akkor a tiltások között csak ez látszik: "Nincs mit nullázni."
    És nincs nullázás gomb
    És nem volt szerverhiba
    És nincs böngészőhiba

  # a nullázás előtt a front betölti (és a 2LC, a result cache és a FileCache megjegyzi) a tavalyi pontokat és kupákat:
  # ha a nullázás nem ürítené a cache-eket, utána is ezek látszanának
  Forgatókönyv: A nullázás után a front azonnal az üres szezont mutatja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E User1" belép
    És a VB tabella, sorrendtől függetlenül:
      | név       | pont | kupák            |
      | E2E User1 | 26   | 1 arany, 1 ezüst |
      | E2E User2 | 26   | 1 arany, 1 ezüst |
      | E2E User3 | 18   | 2 bronz          |
    És a Trófeák oldal nem üres
    Ha a szuperadmin nullázza a szezont
    És "E2E User1" belép
    Akkor a VB tabella, sorrendtől függetlenül:
      | név       | pont | kupa |
      | E2E User1 | 0    |      |
      | E2E User2 | 0    |      |
      | E2E User3 | 0    |      |
    És a Trófeák oldal üres
    És az Aktuális oldalon nem látszik tipp és eredmény
    Ha megnyitja az Eredmények oldalt
    És az Eredmények oldalon a tavalyi évre kattint
    Akkor az oldalon látszik a tavalyi évre: "E2E archív {Y}"
    És nem volt szerverhiba
    És nincs böngészőhiba

  # a nullázás után a következő szezon ugyanúgy megy: az 1. hétvége jövőbe tett időmérőjére tippelünk, az admin
  # rögzíti az eredményt, a pontok az Aktuális oldalon jelennek meg (a VB tabellát nem nézzük: az alternatív pont a
  # futamhoz kötődik, a teljes hétvége-számolást a @weekend és @season feature-ök fedik)
  Forgatókönyv: A nullázás után az új szezon első időmérőjére tippelni és pontot kapni lehet
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És a szuperadmin nullázza a szezont
    És a szuperadmin az 1. hétvége időmérőjét 2 óra, futamát 1 nap múlvára teszi
    # az Aktuális oldal most a tavaly tippelt és eredményes 1. hétvégét mutatja: a régi tippekből és eredményből semmi
    Ha "E2E User1" belép
    Akkor az Aktuális oldalon nem látszik tipp és eredmény
    És az "IDŐMÉRŐ" táblán nincs tipp, csak ez a megjegyzés: "Még senki sem tippelt."
    Ha "E2E User1" leadja a tippjeit:
      | esemény | helyezések                              | safety car |
      | Időmérő | VER NOR LEC PIA HAM RUS SAI ALO GAS ALB |            |
    És az admin rögzíti az "Időmérő" eredményét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális oldalon:
      | név       | IDŐMÉRŐ |
      | E2E User1 | 40      |
    És nem volt szerverhiba
    És nincs böngészőhiba

  Forgatókönyv: A nullázás után az összes pont újraszámolása hibátlanul lefut, és nem hoz létre kupát
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És a szuperadmin nullázza a szezont
    Ha az admin újraszámolja az összes pontot
    Akkor nincs kupa és alternatív pont az adatbázisban
    És nem volt szerverhiba
    És nincs böngészőhiba

  # --- az élő checklist (kulcsonként a [data-check] sor és a [data-status] állapota)

  Forgatókönyv: Az archív bejegyzés pont: megvan, illetve hiányzik
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "archive" ellenőrzés állapota: "ok", szövege: "„{Y}” archív Információ-bejegyzés megvan"
    Amennyiben a tavalyi szezon lezárult, de nincs archív bejegyzés
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "archive" ellenőrzés állapota: "fail", szövege: "Hiányzik a „{Y}” archív Információ-bejegyzés"

  Forgatókönyv: A hash-es kép-URL az archív bejegyzésben figyelmeztet, de nem tiltja a nullázást
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "hashed_images" ellenőrzés állapota: "ok"
    Ha a szuperadmin a tavalyi archív Információ tartalmát átírja erre: '<p>archív</p><img src="/build/images/trophy_gold.c61f05af.png">'
    És megnyitja a Szezonváltás oldalt
    Akkor az "hashed_images" ellenőrzés állapota: "fail", szövege: "/build/images/trophy_gold.c61f05af.png"
    És van nullázás gomb
    Ha a szuperadmin a tavalyi archív Információ tartalmát átírja erre: '<p>archív</p><img src="/build/images/trophy/trophy_gold.svg">'
    És megnyitja a Szezonváltás oldalt
    Akkor az "hashed_images" ellenőrzés állapota: "ok"

  Forgatókönyv: Az alter-bejegyzés pont csak tájékoztat, a hash-es képet abban is kiszúrja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "alter_archive" ellenőrzés állapota: "info", szövege: "„alter-{Y}” bejegyzés: nincs"
    Ha a szuperadmin felvesz egy új információs oldalt:
      | cím      | E2E alter                                                  |
      | slug     | alter-{tavalyi év}                                         |
      | tartalom | <img src="/build/images/trophy_gold.c61f05af.png">         |
    És megnyitja a Szezonváltás oldalt
    Akkor az "alter_archive" ellenőrzés állapota: "info", szövege: "„alter-{Y}” bejegyzés: létezik"
    És az "hashed_images" ellenőrzés állapota: "fail", szövege: "/build/images/trophy_gold.c61f05af.png"

  Forgatókönyv: A régi dátumú, eredmény nélküli események a nullázás után jelennek meg
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "stale_events" ellenőrzés állapota: "ok"
    Ha beírja a megerősítő kódot, és a Szezon nullázása gombra kattint
    Akkor megjelenik a siker-üzenet
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "stale_events" ellenőrzés állapota: "fail", szövege: "4 esemény régi dátummal, eredmény nélkül"
    Ha a szuperadmin a 2. hétvége eseményeit 3 évvel későbbre teszi
    És megnyitja a Szezonváltás oldalt
    Akkor az "stale_events" ellenőrzés állapota: "fail", szövege: "2 esemény régi dátummal, eredmény nélkül"
    Ha a szuperadmin az 1. hétvége eseményeit 3 évvel későbbre teszi
    És megnyitja a Szezonváltás oldalt
    Akkor az "stale_events" ellenőrzés állapota: "ok", szövege: "Nincs régi dátumú, eredmény nélküli esemény"

  Forgatókönyv: A bajnoki pontok sora követi az admin táblát
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "championship_points" ellenőrzés állapota: "info", szövege: "Bajnoki pontok: 15-11-9-7-6-5-4-2-1-0"
    Ha a szuperadmin átírja a bajnoki pontokat:
      | helyezés | pont |
      | 1        | 25   |
      | 10       | 3    |
    És megnyitja a Szezonváltás oldalt
    Akkor az "championship_points" ellenőrzés állapota: "info", szövege: "Bajnoki pontok: 25-11-9-7-6-5-4-2-1-3"

  Forgatókönyv: A nyeremény pont a nevezés állapotát és a díjsorok számát mutatja
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "prize" ellenőrzés állapota: "info", szövege: "Nyeremény: lezárva, 3 díjsor"
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És bepipálja: "Még nyitott a nevezés"
    És elmenti a nyeremény beállításait
    És megnyitja a Szezonváltás oldalt
    Akkor az "prize" ellenőrzés állapota: "info", szövege: "Nyeremény: nevezés nyitva, 3 díjsor"

  Forgatókönyv: A résztvevők száma követi a bajnokság tagjait
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "participants" ellenőrzés állapota: "info", szövege: "Résztvevők: 3 „bajnokság tagja”"
    Ha a szuperadmin megnyitja az adminban a "E2E User1" felhasználót
    És kiveszi a bajnokságból
    És elmenti
    És megnyitja a Szezonváltás oldalt
    Akkor az "participants" ellenőrzés állapota: "info", szövege: "Résztvevők: 2 „bajnokság tagja”"

  # a várt lista a seedelt aktív pilótákból és a config/packages/f1tipp_teams.yaml-ből számolódik (nem fix kódok),
  # így a yaml évi frissítése nem töri el a tesztet
  Forgatókönyv: A csapat nélküli aktív pilóták a f1tipp_teams.yaml szerint
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a "drivers_without_team" ellenőrzés a yaml szerint a hiányzó aktív pilótákat jelzi
    Ha a szuperadmin megnyitja az adminban a "TSU" pilótát
    És kikapcsolja az aktív jelölést
    És elmenti
    És megnyitja a Szezonváltás oldalt
    Akkor a "drivers_without_team" ellenőrzés a yaml szerint a hiányzó aktív pilótákat jelzi
    Ha a szuperadmin felvesz egy új pilótát:
      | név         | Teszt Pilóta |
      | rövidítés   | ZZZ          |
      | pont        | 0            |
      | aktív       | igen         |
    És megnyitja a Szezonváltás oldalt
    Akkor az "drivers_without_team" ellenőrzés állapota: "fail", szövege: "ZZZ"
    És a "drivers_without_team" ellenőrzés a yaml szerint a hiányzó aktív pilótákat jelzi

  # {C} az új szezon éve: az események között a legkésőbbi dátum éve (késő decemberben a seedelt jövőbeli események már a következő évbe esnek)
  Forgatókönyv: A szabályzat címének évszáma az új szezon éve
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "rules_title" ellenőrzés állapota: "fail", szövege: "címe nem tartalmazza: {C}"
    Ha a szuperadmin a szabályok oldal címét átírja erre: "Szabályzat {C}"
    És megnyitja a Szezonváltás oldalt
    Akkor az "rules_title" ellenőrzés állapota: "ok", szövege: "címe tartalmazza: {C}"
    Ha a szuperadmin a szabályok oldal címét átírja erre: "Szabályzat {C-1}"
    És megnyitja a Szezonváltás oldalt
    Akkor az "rules_title" ellenőrzés állapota: "fail", szövege: "címe nem tartalmazza: {C}"

  Forgatókönyv: A RAPIDAPI_KEY be van állítva
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "rapidapi_key" ellenőrzés állapota: "ok"

  Forgatókönyv: A kézi emlékeztetők mind ott vannak, nem pipálhatók
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor a kézi emlékeztetők láthatók: backup, deploy_code, after_deploy, driver_sync, championship_sidebar
    És a checklistben nincs jelölőnégyzet

  # minden link az EasyAdmin megfelelő oldalára visz (a Szezonváltás oldalról indulva); a lépés a link után visszalép az oldalra
  Forgatókönyv: A checklist linkjei a megfelelő admin oldalra visznek
    Amennyiben a tavalyi szezon lezárult, az archív bejegyzés megvan
    És "E2E Superadmin" belép
    Ha megnyitja a Szezonváltás oldalt
    Akkor az "archive" ellenőrzés linkje ide visz: "Information" "new"
    És az "stale_events" ellenőrzés linkje ide visz: "Event" "index"
    És az "championship_points" ellenőrzés linkje ide visz: "ChampionshipPoint" "index"
    És az "prize" ellenőrzés linkje ide visz: "PrizeDraw" "edit" #1
    És az "participants" ellenőrzés linkje ide visz: "User" "index"
    És az "drivers_without_team" ellenőrzés linkje ide visz: "Driver" "index"
    És az "rules_title" ellenőrzés linkje ide visz: "Information" "edit" #23
    És az "driver_sync" ellenőrzés linkje ide visz: "Driver" "index"
