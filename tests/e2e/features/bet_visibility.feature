# language: hu
@visibility
Jellemző: Mások tippjeinek láthatósága
  Amíg a user nem tippelt egy eseményre (és a határidő sem járt le), a többiek tippjét nem láthatja, csak azt,
  hogy ki tippelt már (src/LegacyService/ResultTable/ResultTable.php: only_users -> only_bets -> full).
  A "Tippnélküliek" sor csak akkor jelenik meg, ha a tippelők több mint fele már tippelt.
  A seed: E2E User2 időmérő tippje NOR VER LEC PIA HAM RUS SAI ALO GAS ALB,
          E2E User3 időmérő tippje TSU STR OCO HUL VER NOR LEC PIA HAM RUS.

  Forgatókönyv: Tippelés előtt a user csak azt látja, ki tippelt, a tippeket nem
    Amennyiben E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem
    És "E2E User1" belép
    Akkor az "IDŐMÉRŐ" táblán nincs tipp, csak ezek a megjegyzések:
      | Tippesek: E2E User2, E2E User3 |
      | Tippnélküliek: E2E User1       |
    És az oldalon sehol nem látszik a "TSU" pilóta tippje
    És a "FUTAM" táblán nincs tipp, csak ez a megjegyzés: "Még senki sem tippelt."

  Forgatókönyv: A vendég is csak azt látja, ki tippelt
    Amennyiben E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem
    Ha vendégként megnyitja az Aktuális oldalt
    Akkor az "IDŐMÉRŐ" táblán nincs tipp, csak ezek a megjegyzések:
      | Tippesek: E2E User2, E2E User3 |
      | Tippnélküliek: E2E User1       |

  Forgatókönyv: A saját tipp után mindenki tippje látszik, de csak annál az eseménynél
    Amennyiben E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem
    És "E2E User1" belép
    Ha a Tippelek oldalon az "Időmérő" fülre vált
    És kitölti a helyezéseket: "VER NOR LEC PIA HAM RUS SAI ALO GAS ALB"
    És elküldi a tippet
    Akkor a tipp rögzítve
    És az "IDŐMÉRŐ" táblán ezek tippje látszik: "E2E User1, E2E User2, E2E User3"
    És az "IDŐMÉRŐ" táblán "E2E User2" tippje: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    És az "IDŐMÉRŐ" táblán "E2E User3" tippje: "TSU STR OCO HUL VER NOR LEC PIA HAM RUS"
    # a futamra még senki sem tippelt: ott továbbra sincs tipp-sor
    És a "FUTAM" táblán ezek tippje látszik: ""

  Forgatókönyv: Aki maga nem tippelt, az továbbra sem látja a tippeket
    Amennyiben E2E User2 és E2E User3 már tippelt az időmérőre, E2E User1 még nem
    És "E2E Admin" belép
    Akkor az "IDŐMÉRŐ" táblán ezek tippje látszik: ""

  Forgatókönyv: A határidő után saját tipp nélkül is látszik mindenki tippje
    Amennyiben E2E User2 és E2E User3 tippelt az időmérőre, E2E User1 nem, és a határidő lejárt
    És "E2E User1" belép
    Akkor az "IDŐMÉRŐ" táblán "E2E User2" tippje: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    És az "IDŐMÉRŐ" táblán "E2E User3" tippje: "TSU STR OCO HUL VER NOR LEC PIA HAM RUS"
    És az "IDŐMÉRŐ" táblán nincs "E2E User1" tipp-sora
