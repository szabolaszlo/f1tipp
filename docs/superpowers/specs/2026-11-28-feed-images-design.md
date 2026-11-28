# F1Világ hírek borítóképe a WordPress REST API-ból – terv

Branch: `feedfix`

## Miért

Az f1vilag.hu 2026-11-21 és 2026-11-27 között új WordPressre költözött (a poszt-id-k `?p=2218xx`-ről `?p=1000xxx`-re
ugrottak). Az új RSS `<description>`-jében nincs borítókép `<img>`, és a lábléc magyar lett („A … bejegyzés először
F1VILÁG.HU-én jelent meg.”). Az `F1VilagRepository` ezért üres képet ment, és a lábléc bent marad a bevezetőben.
A `content:encoded`-ben csak néha van kép, és az is csapatlogó, nem borítókép.

## Célok / nem célok

- Cél: a hírek újra kapjanak borítóképet, a bevezetőből kerüljön ki a magyar és az angol lábléc is.
- Cél: a már tárolt, kép nélküli hírek a következő gyűjtéskor maguktól javuljanak, backfill nélkül.
- Cél: e2e lefedettség a gyűjtésre (cron → főoldal), külső hálózat nélkül.
- Nem cél: a News.vue megjelenésének változtatása.
- Anti-cél: egy elérhetetlen REST API ne törölje a meglévő képet, és ne okozzon hibát.

## Megközelítések

- **A) WP REST API, egy kérés – választott.** `GET /wp-json/wp/v2/posts?include=<id-k>&per_page=<n>&_embed=wp:featuredmedia&_fields=id,_links,_embedded`.
  A poszt-id-t az RSS `guid`-jából (`?p=<id>`) vesszük, így a párosítás pontos. Ez WP core, nem kell hozzá plugin.
  Cronfutásonként egy plusz kérés (15 percenként).
- B) A cikkoldal `og:image` meta tagje: cikkenként egy HTML-letöltés (10 kérés) és HTML-parse.
- C) A Yoast `yoast_head_json.og_image` a REST-ben: pluginfüggő, ha a Yoastot lecserélik, eltörik.

## Viselkedés

- Képsorrend: 1. a REST kiemelt képe, ebből a `medium_large` méret (768 px; a lista 120 px széles képet mutat), ha az
  nincs, akkor az eredeti `source_url`; 2. a description első `<img>`-je (régi formátum, illetve ha a REST nem érhető el);
  3. üres.
- Csak `http(s)` URL fogadható el; a hibás JSON vagy a hiányzó / tiltott média (`rest_forbidden`) üres képet ad.
- Lábléc: az a `<p>`, amelyben „appeared first on” vagy „bejegyzés először” áll, kikerül. A minta nem léphet át
  bekezdéshatárt, hogy ne vigye magával az „A …” kezdetű bevezetőt.
- Tárolás (`Feed\Storage\Doctrine`): a már létező sornál a cím és a bevezető frissül a feedből, a kép csak akkor, ha a
  feedből nem üres kép jött. Az id, a link és a dátum nem változik. A cron továbbra is csak akkor ment, ha a legújabb
  id változott (új hír érkezett).
- Cache: az írás ORM-en át megy (2LC-frissítéssel, mint eddig a beszúrás), a hírek nem FileCache-ből jönnek.

## Szerkezet

- `Feed\Fetcher\IFetcher` (`fetch(url): ?string`) és `HttpFetcher` (`file_get_contents`, UA, 15 s timeout; a mostani
  `XMLCollector` letöltése). Az `XMLCollector` és az új `Feed\Image\WordPressFeaturedImages` is ezt használja.
- e2e: `App\E2e\E2eFeedFetcher` (csak `services_e2e.yaml`-ben van bekötve) a feed és a REST URL helyett a
  `var/e2e-feed/feed.xml` és `posts.json` fájlt adja, hiányzó fájlnál `null`-t; más URL-re kivételt dob.
  A fájlokat a feed-szenáriók írják.

## Forgatókönyvek (e2e, `@feed`)

A feedben négy hír van: A (új formátum, van kiemelt kép), B (új formátum, van kiemelt kép), C (új formátum, nincs
kiemelt kép), D (régi formátum: beágyazott `<img>`, angol lábléc, a REST nem ismeri).

1. Új formátum: A képe a REST `medium_large` URL-je, a bevezetőben nincs magyar lábléc.
2. Kiemelt kép nélküli hír (C) kép nélkül jelenik meg, hiba nélkül.
3. Régi formátum (D): a kép a beágyazott `<img>`-ből jön, az angol lábléc kikerül.
4. Elérhetetlen REST API: a gyűjtés lefut (`OK`), A kép nélkül mentődik, D képe megmarad, B tárolt képe nem vész el.
5. Öngyógyítás: a korábban kép nélkül, lábléccel tárolt B a következő gyűjtéskor megkapja a képét és a tiszta bevezetőt.
6. Elérhetetlen feed: `NO FEED ITEMS`, a meglévő hírek megmaradnak.

Unit (`F1VilagRepositoryTest`, `WordPressFeaturedImagesTest`): REST-kérés URL-je (id-k, `_embed`), méret-fallback,
hibás JSON, nem http URL, `rest_forbidden` média, nincs id → nincs kérés, a lábléc-minta nem nyeli el az „A …”
kezdetű bevezetőt.
