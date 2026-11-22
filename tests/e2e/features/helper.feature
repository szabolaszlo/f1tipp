# language: hu
@helper
Jellemző: E2E segéd önellenőrzése
  Az E2eHelper hibagyűjtése (helpers/E2eHelper.js) csak akkor ér valamit, ha a váratlan hibákon tényleg elbukik,
  a várt zajon (vendégként a 401-es /is_logged) viszont nem.

  Forgatókönyv: Egy saját hostról érkező 404-es erőforrás böngészőhibának számít
    Ha megnyitja a "/is_logged" oldalt
    És az oldal lekér egy nem létező saját fájlt: "/build/nincs-ilyen-fajl.js"
    Akkor a hibagyűjtő böngészőhibát jelez

  Forgatókönyv: Vendégként a 401-es /is_logged várt zaj
    Amennyiben nyitott hétvége van
    Ha megnyitja a főoldalt
    Akkor nincs belépve senki
    És nincs böngészőhiba
