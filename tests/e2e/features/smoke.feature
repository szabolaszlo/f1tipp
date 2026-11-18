# language: hu
@smoke
Jellemző: Smoke
  Az alkalmazás nulláról seedelt adatbázissal elindul.

  Forgatókönyv: A főoldal betöltődik az adatbázis újraépítése után
    Amennyiben nyitott hétvége van
    Ha megnyitja a főoldalt
    Akkor az oldalsávban látszik a VB tabella, benne: "E2E User1"
    És nincs böngészőhiba
