# language: hu
@fix
Jellemző: Eredmény javítása és törlése
  Egy tévesen rögzített eredmény átírása vagy törlése után a pontoknak, a tabellának, a kupáknak és az
  oldalsáv rekordjainak újra kell számolódniuk. 2 eseményes hétvége; a sprintest a sprint_weekend.feature fedi.
  A seedelt tippek mint a scoring.feature-ben; az eredmény: időmérő VER NOR LEC PIA HAM RUS SAI ALO GAS ALB,
  futam ugyanez + safety car: Igen. A javítás az időmérő eredményét NOR VER LEC PIA HAM RUS SAI ALO GAS ALB-ra írja:
  így E2E User2 időmérő tippje lesz pontos (40), E2E User1-é 34.

  Háttér:
    Amennyiben lezajlott hétvége van eredményekkel

  Forgatókönyv: Az időmérő eredmény javítása újraszámolja a pontokat, a tabellát és a kupákat
    Akkor a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User1 | 14   |           | arany |
      | E2E User2 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
    Ha az admin az "Időmérő" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a pontok az Aktuális oldalon:
      | név       | IDŐMÉRŐ |
      | E2E User1 | 34      |
      | E2E User2 | 40      |
      | E2E User3 | 6       |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 14   |           | arany |
      | E2E User1 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
    És a Trófeák oldal kupatáblája:
      | név       | kupa  |
      | E2E User1 | ezüst |
      | E2E User2 | arany |
      | E2E User3 | bronz |

  Forgatókönyv: Az oldalsáv időmérő rekordja követi a javított eredményt
    Akkor az "Időmérők Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 40   |
      | E2E User2 | 34   |
      | E2E User3 | 6    |
    Ha az admin az "Időmérő" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor az "Időmérők Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 40   |
      | E2E User1 | 34   |
      | E2E User3 | 6    |

  # Korábban hibás volt: a 'topWeekendUsers' cache-t semmi nem ürítette, ezért javítás után a régi pontok maradtak.
  Forgatókönyv: Az oldalsáv hétvégi rekordja követi a javított eredményt
    Akkor a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User1 | 70   |
      | E2E User2 | 67   |
      | E2E User3 | 15   |
    Ha az admin az "Időmérő" eredményét erre javítja: "NOR VER LEC PIA HAM RUS SAI ALO GAS ALB"
    Akkor a "Hétvégék Legjobbjai (TOP 3):" rekord:
      | név       | pont |
      | E2E User2 | 73   |
      | E2E User1 | 64   |
      | E2E User3 | 15   |

  # futam: User1 30, User2 33, User3 9 -> bajnoki pont User2 14, User1 10, User3 8
  Forgatókönyv: Az eredmény törlése után a hétvége nélküle számolódik
    Ha az admin törli az "Időmérő" eredményét
    # nincs eredmény, de a határidő lejárt: a tippek látszanak, pont és pontos / közeli jelölés nélkül
    Akkor az "IDŐMÉRŐ" táblán "E2E User1" tippje pont és jelölés nélkül látszik
    És a pontok az Aktuális oldalon:
      | név       | FUTAM |
      | E2E User2 | 33    |
    És a VB tabella:
      | név       | pont | lemaradás | kupa  |
      | E2E User2 | 14   |           | arany |
      | E2E User1 | 10   | + 4       | ezüst |
      | E2E User3 | 8    | + 6       | bronz |
