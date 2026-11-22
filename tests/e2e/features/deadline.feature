# language: hu
@deadline
Jellemző: Tippelési határidő
  Az esemény kezdete után nem lehet rá tippelni; a szerver a saját órájához méri a határidőt.

  Forgatókönyv: Lejárt eseménynél nincs tippelő űrlap
    Amennyiben az időmérő határideje lejárt, a futam még nyitott
    És "E2E User1" belép
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    Akkor a tippelési időszak lejárt üzenet látszik
    És nincs tippelő űrlap
    Ha a "Futam" fülre vált
    Akkor van tippelő űrlap

  Forgatókönyv: Ha kitöltés közben jár le a határidő, a tippet elutasítja
    Amennyiben az időmérő határideje 20 másodperc múlva lejár
    És "E2E User1" belép
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És megvárja, amíg a szerver órája szerint lejár az "Időmérő" határideje
    És elküldi a tippet
    Akkor a tippelési időszak lejárt üzenet látszik
    És az "IDŐMÉRŐ" táblán nincs "E2E User1" tipp-sora

  Forgatókönyv: A hétvége eseményeinek cache-e nem marad meg az adatbázis cseréje után
    Amennyiben nyitott sprint hétvége van
    És "E2E User1" belép
    Akkor a Tippelek oldal fülei sorban: "Sprint Futam, Időmérő, Futam"
    Amennyiben az időmérő határideje lejárt, a futam még nyitott
    Ha megnyitja a Tippelek oldalt
    És újratölti az oldalt
    Akkor a fülek sorban: "Időmérő, Futam"
