# language: hu
@prize_draw
Jellemző: Nyeremény modul az adminból
  A Nyeremény modul (oldalsáv, csak belépve) az admin "Nyeremény" menüjéből jön (prize_draw, egyetlen sor, id = 1):
  helyezés -> összeg sorok és egy szabad megjegyzés. Ha "Még nyitott a nevezés", a modul csak a résztvevők számát
  mutatja: a bajnokság tagjai (is_alter_champs), élőben számolva.
  Az e2e seed a mostani éles értékeket tölti be (src/E2e/E2eFixtures.php: PRIZES, PRIZE_NOTE), lezárt nevezéssel.
  A bajnokság tagjai: E2E User1–3; az admin és a szuperadmin nem.
  A vendég nem látja a modult: pages.feature, "A vendég látja a híreket ...".

  Háttér:
    Amennyiben lezajlott hétvége van eredményekkel

  Forgatókönyv: Belépve a Nyeremény modul a seed értékeit mutatja
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 3:9,600", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"

  Forgatókönyv: Az összeg és a megjegyzés átírása
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És az 1. helyezés összegét átírja erre: 30000
    És a nyeremény megjegyzését átírja erre: "10 résztvevő X 5000 = 50000"
    És elmenti a nyeremény beállításait
    Akkor megjelenik az admin üzenet: "Nyeremény mentve"
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:30,000 2:14,400 3:9,600", megjegyzés: "10 résztvevő X 5000 = 50000"

  Forgatókönyv: Nyeremény sor hozzáadása és törlése
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És felvesz egy nyeremény sort: 4. helyezés, 5000 Ft
    És elmenti a nyeremény beállításait
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 3:9,600 4:5,000", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És törli a 3. helyezés nyeremény sorát
    És elmenti a nyeremény beállításait
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 4:5,000", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"

  # egy mentésben: a Doctrine előbb beszúr és módosít, csak utána töröl, ezért ezek nem ütközhetnek a régi sorokkal
  Forgatókönyv: Törölt helyezés újrafelvétele, átsorszámozás és csere egy-egy mentésben
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És törli a 3. helyezés nyeremény sorát
    És felvesz egy nyeremény sort: 3. helyezés, 9000 Ft
    És elmenti a nyeremény beállításait
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 3:9,000", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És felcseréli az 1. és a 2. helyezést
    És elmenti a nyeremény beállításait
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:14,400 2:24,000 3:9,000", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És törli a 2. helyezés nyeremény sorát
    És a 3. helyezést átírja erre: 2
    És elmenti a nyeremény beállításait
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:14,400 2:9,000", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"

  Forgatókönyv: Nyitott nevezésnél csak a résztvevők száma látszik
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És bepipálja: "Még nyitott a nevezés"
    És elmenti a nyeremény beállításait
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modulban csak ez látszik: "Jelenleg 3 résztvevő van, a díjazás még nincs kiszámolva."

  Forgatókönyv: Nyitott nevezésnél a résztvevők száma követi a bajnokság tagjait
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És bepipálja: "Még nyitott a nevezés"
    És elmenti a nyeremény beállításait
    És a szuperadmin felvesz egy új felhasználót:
      | név             | E2E Új Tag   |
      | felhasználónév  | e2e_uj_tag   |
      | szerepkör       | ROLE_USER    |
      | jelszó          | e2e-pass     |
      | bajnokság tagja | igen         |
    És "E2E User1" belép
    Akkor a Nyeremény modulban csak ez látszik: "Jelenleg 4 résztvevő van, a díjazás még nincs kiszámolva."
    Ha a szuperadmin megnyitja az adminban a "E2E User2" felhasználót
    És kiveszi a bajnokságból
    És elmenti
    És "E2E User1" belép
    Akkor a Nyeremény modulban csak ez látszik: "Jelenleg 3 résztvevő van, a díjazás még nincs kiszámolva."

  Forgatókönyv: A nevezés lezárása után ismét az összegek és a megjegyzés látszik
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És bepipálja: "Még nyitott a nevezés"
    És elmenti a nyeremény beállításait
    És a szuperadmin megnyitja a nyeremény beállításait
    És kiveszi a pipát: "Még nyitott a nevezés"
    És elmenti a nyeremény beállításait
    És "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 3:9,600", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"

  # két új sor ugyanazzal a helyezéssel: az egyedi index helyett validációs hiba (nem 500)
  Forgatókönyv: Duplikált helyezés és negatív összeg nem menthető
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És felvesz egy nyeremény sort: 4. helyezés, 1000 Ft
    És felvesz egy nyeremény sort: 4. helyezés, 2000 Ft
    És menteni próbálja a nyeremény beállításait
    Akkor a mentés hibát jelez: "Ehhez a helyezéshez már van összeg megadva."
    És nem volt szerverhiba
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És felvesz egy nyeremény sort: 2. helyezés, 1000 Ft
    És menteni próbálja a nyeremény beállításait
    Akkor a mentés hibát jelez: "Ehhez a helyezéshez már van összeg megadva."
    És nem volt szerverhiba
    Ha a szuperadmin megnyitja a nyeremény beállításait
    És az 1. helyezés összegét átírja erre: -1
    És menteni próbálja a nyeremény beállításait
    Akkor a mentés hibát jelez: "Az összeg nem lehet negatív."
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 3:9,600", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"

  # egyetlen beállítás (id = 1): új nem hozható létre, nem törölhető, lista sincs; csak a szuperadmin szerkesztheti
  Forgatókönyv: A nyeremény beállítás nem hozható létre újra és nem törölhető
    Amennyiben "E2E Superadmin" belép
    Akkor a kérések válasza:
      | metódus | útvonal                     | státusz |
      | GET     | admin PrizeDraw edit 1      | 200     |
      | GET     | admin PrizeDraw new         | 403     |
      | GET     | admin PrizeDraw index       | 403     |
      | POST    | admin PrizeDraw delete 1    | 403     |
    Amennyiben "E2E Admin" belép
    Akkor a kérések válasza:
      | metódus | útvonal                     | státusz |
      | GET     | admin PrizeDraw edit 1      | 403     |
    Amennyiben "E2E User1" belép
    Akkor a Nyeremény modul: "1:24,000 2:14,400 3:9,600", megjegyzés: "12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000"
