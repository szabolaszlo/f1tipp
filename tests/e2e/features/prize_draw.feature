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
