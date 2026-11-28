# language: hu
@feed
Jellemző: Hírgyűjtés az f1vilag.hu-ról
  A 2026-11 óta érvényes feedben nincs borítókép, és a lábléc magyar. A kép a WordPress REST API kiemelt képe
  (768 px-es változat), ennek hiányában a leírásba ágyazott kép. A feed és az API e2e-ben fájlból jön
  (src/E2e/E2eFeed.php): A és B hírnek van kiemelt képe, C-nek nincs, D régi formátumú (beágyazott kép,
  angol lábléc). Terv: docs/superpowers/specs/2026-11-28-feed-images-design.md

  Forgatókönyv: Az új formátumú hír a kiemelt képét kapja, a bevezetőjéből kimarad a magyar lábléc
    Amennyiben az f1vilag.hu feed és a WordPress API is elérhető
    Ha lefut a hírgyűjtés
    És megnyitja a főoldalt
    Akkor a hírgyűjtés válasza: "OK"
    És az "E2E feed: új formátumú hír" hír képe: "https://f1vilag.hu/wp-content/uploads/e2e-a-768x512.jpg"
    És az "E2E feed: új formátumú hír" hír bevezetője: "A Mercedes diktálta a tempót a pénteki edzésen."
    És nem volt szerverhiba
    És nincs böngészőhiba

  Forgatókönyv: A kiemelt kép nélküli hír kép nélkül jelenik meg
    Amennyiben az f1vilag.hu feed és a WordPress API is elérhető
    Ha lefut a hírgyűjtés
    És megnyitja a főoldalt
    Akkor az "E2E feed: kiemelt kép nélküli hír" hír kép nélkül jelenik meg
    És az "E2E feed: kiemelt kép nélküli hír" hír bevezetője: "Rövid hír kép nélkül."
    És nem volt szerverhiba
    És nincs böngészőhiba

  Forgatókönyv: A régi formátumú hír képe továbbra is a leírásba ágyazott kép, az angol lábléc kimarad
    Amennyiben az f1vilag.hu feed és a WordPress API is elérhető
    Ha lefut a hírgyűjtés
    És megnyitja a főoldalt
    Akkor az "E2E feed: régi formátumú hír" hír képe: "https://f1vilag.hu/wp-content/uploads/e2e-d.jpg"
    És az "E2E feed: régi formátumú hír" hír bevezetője: "Régi formátumú bevezető."
    És nem volt szerverhiba
    És nincs böngészőhiba

  Forgatókönyv: A korábban kép nélkül, lábléccel mentett hír a következő gyűjtéskor megjavul
    Amennyiben az "E2E feed: utólag képet kapó hír" hír korábban kép nélkül, lábléccel mentődött
    Ha lefut a hírgyűjtés
    És megnyitja a főoldalt
    Akkor a hírgyűjtés válasza: "OK"
    És az "E2E feed: utólag képet kapó hír" hír képe: "https://f1vilag.hu/wp-content/uploads/e2e-b-768x512.jpg"
    És az "E2E feed: utólag képet kapó hír" hír bevezetője: "Antonelli nyerte a második szabadedzést."
    És nem volt szerverhiba
    És nincs böngészőhiba

  Forgatókönyv: Elérhetetlen WordPress API mellett a hírek mentődnek, a meglévő képek megmaradnak
    Amennyiben az f1vilag.hu feed elérhető, a WordPress API nem, és "E2E feed: utólag képet kapó hír" képpel már tárolva van
    Ha lefut a hírgyűjtés
    És megnyitja a főoldalt
    Akkor a hírgyűjtés válasza: "OK"
    És az "E2E feed: új formátumú hír" hír kép nélkül jelenik meg
    És az "E2E feed: új formátumú hír" hír bevezetője: "A Mercedes diktálta a tempót a pénteki edzésen."
    És az "E2E feed: utólag képet kapó hír" hír képe: "https://f1vilag.hu/wp-content/uploads/e2e-b-stored.jpg"
    És az "E2E feed: régi formátumú hír" hír képe: "https://f1vilag.hu/wp-content/uploads/e2e-d.jpg"
    És nem volt szerverhiba
    És nincs böngészőhiba

  Forgatókönyv: Elérhetetlen feed mellett a meglévő hírek megmaradnak
    Amennyiben az f1vilag.hu feed nem érhető el
    Ha lefut a hírgyűjtés
    És megnyitja a főoldalt
    Akkor a hírgyűjtés válasza: "NO FEED ITEMS"
    És látszik a hír: "E2E hír: lezárult a szezon előtti teszt"
    És nem volt szerverhiba
    És nincs böngészőhiba
