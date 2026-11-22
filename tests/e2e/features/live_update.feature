# language: hu
@live
Jellemző: Élő frissítés
  A nyitott oldal (assets/js/module_reload.js) 10 mp-enként lekéri az entitásszámokat, és ha változott,
  újratölti az érintett modult. Egy másik user (külön böngésző-session) ír / tippel, nekünk oldal-újratöltés
  nélkül kell látnunk.

  Forgatókönyv: Egy másik user üzenete újratöltés nélkül megjelenik
    Amennyiben nyitott hétvége van
    És "E2E User1" belép
    Ha egy másik böngészőben "E2E User2" üzenetet ír az üzenőfalra: "Üzenet a másik böngészőből"
    Akkor újratöltés nélkül megjelenik az üzenőfalon: "Üzenet a másik böngészőből"

  Forgatókönyv: Egy másik user tippje újratöltés nélkül megjelenik a nyitott Aktuális oldalon
    Amennyiben nyitott hétvége van
    Ha vendégként megnyitja az Aktuális oldalt
    Akkor az "IDŐMÉRŐ" táblán nincs tipp, csak ez a megjegyzés: "Még senki sem tippelt."
    Ha megvárja, hogy az élő frissítés elinduljon
    És egy másik böngészőben "E2E User2" leadja az időmérő tippjét: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor újratöltés nélkül megjelenik az "IDŐMÉRŐ" táblán: "Tippesek: E2E User2"
