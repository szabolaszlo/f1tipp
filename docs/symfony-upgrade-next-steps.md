# Következő lépés: Symfony és sebezhető csomagok frissítése

Átadó jegyzet a `php84` branch után (commit `a334b63`, 2026-11-16). A következő session innen folytatja.

## Kiinduló állapot

- PHP 8.4 (Docker: `.docker/php84/`), Symfony **5.4** (5.4.4x–5.4.5x patch-szint), Doctrine ORM 2.20, Twig 3.30.
- Tesztek zöldek: `php bin/phpunit` → 34 OK; `npm run e2e` → 28 passed, 1 skipped (`@`-csoportok a README-ben).
- `composer audit`: **egyetlen** érintett csomag maradt, az `api-platform/core` 2.7.18 (4 advisory, ebből 2 high).
  A `composer.json` `config.policy.advisories.ignore` blokkja csak a telepítés blokkolását oldja fel erre a csomagra,
  az auditban továbbra is látszik. **Ha az api-platform kikerül, ezt a blokkot is törölni kell.**

| CVE | Súlyosság | Tárgy | Javítva |
|---|---|---|---|
| CVE-2025-31481 | high | GraphQL query security bypass | 3.4.17 / 4.0.22 / 4.1.5 |
| CVE-2025-31485 | high | GraphQL property grant cache | 3.4.17 / 4.0.22 / 4.1.5 |
| CVE-2026-49858 | medium | cross-user attribute leak (JSON:API, HAL) | 4.1.29 / 4.2.25 / 4.3.8 |
| CVE-2026-54164 | medium | relation IRI típusellenőrzés hiánya | 4.1.30 / 4.2.26 / 4.3.12 |

A 2.x ágra nincs javítás; a 3.x/4.x Symfony 6.4+-ot igényel.

## Javasolt első lépés: az API Platform kiváltása

Az API Platformot csak **3 darab, csak olvasó GET végpont** használja (YAML-ban definiálva: `config/api_platform/resources/`):

| Végpont | Erőforrás | Hívja |
|---|---|---|
| `GET /api/events` | `event.yaml` | `assets/js/components/Calendar.vue` |
| `GET /api/feeds` | `feed.yaml` | `assets/js/components/News.vue` |
| `GET /api/information/{id}` | `information.yaml` | `assets/js/components/Rules.vue` (fix id: 23, lásd `src/E2e/E2eFixtures.php`) |

Ha ezeket sima Symfony controllerek (JSON válasz) váltják ki, akkor:
- megszűnik mind a 4 advisory, és a `policy` kivétel törölhető;
- eltűnik a logokban látható deprecationök túlnyomó része (több ezer `ApiPlatform\...` sor, a PHP 8.4 „Implicitly marking parameter as nullable” is innen jön, és a `bin/console` stderr-zaja is);
- eltűnik a Symfony-frissítés legnagyobb blokkolója.

Lépések: előbb nézd meg a mostani válaszformátumot (`curl http://127.0.0.1:8000/api/events` stb.: a hydra/JSON-LD mezőket, pl. `hydra:member`, a Vue komponensek is olvashatják), hogy az új controller ugyanazt adja vissza, vagy a Vue-t is igazítsd. Ezután jöhet a bundle (`config/bundles.php`), a `config/packages/api_platform.yaml`, a `config/routes/api_platform.yaml`, a `config/api_platform/` és a csomag eltávolítása. A `nelmio/cors-bundle` is csak az API miatt lehet itt, ezt ellenőrizni kell.

## Symfony frissítési útvonal

5.4 → **6.4 LTS** → (opcionálisan) **7.4 LTS**. A 7.4 PHP 8.2+, a 8.x PHP 8.4+ verziót igényel, ami már adott.
A szokásos menet: az összes 5.x deprecation kijavítása 5.4-en, utána `composer.json` `extra.symfony.require` és a `symfony/*` constraintek léptetése.

### Javítandó deprecationök 5.4-en (az e2e log alapján, darabszámmal)

| Db | Deprecation | Hol |
|---|---|---|
| 572 | `Symfony\Component\Cache\DoctrineProvider` | Doctrine result/query cache konfig (`config/packages/doctrine.yaml`), illetve `getResultCacheImpl()` a `UserChampionshipOrderResolver`-ben és az `E2eFixtures`-ben (`deleteAll()`); PSR-6 cache poolra kell átállni |
| 281×3 | `session.storage.*` service-ek | `framework.session.storage_factory_id: session.storage.factory.native` |
| 280 | `KernelEvent::isMasterRequest()` | `isMainRequest()`, pl. `src/EventSubscriber/RequestSubscriber.php` |
| 106 | `AbstractController::getDoctrine()` | `src/Controller/Module/*`: `ManagerRegistry`/`EntityManagerInterface` injektálása |
| 37 | `AbstractController::get()` | konstruktor-/metódus-injektálás |
| 23 | `User` nem implementálja a `PasswordAuthenticatedUserInterface`-t | `src/Entity/User.php` |
| 22 | `getUserIdentifier()` hiányzik | `src/Entity/User.php` (a `getUsername()` helyett) |
| 3 | `RouteCollectionBuilder` a `Kernel::configureRoutes()`-ban | `src/Kernel.php` → `RoutingConfigurator` |
| – | `security.encoders` | `config/packages/security.yaml` → `password_hashers` |
| – | `UserPasswordEncoderInterface` | a `LoginFormAuthenticator` használja, lásd lent |
| – | `doctrine.orm.enable_lazy_ghost_objects` nincs beállítva | `config/packages/doctrine.yaml` |
| – | Twig `spaceless` filter | `templates/admin/result_new.html.twig`, `result_edit.html.twig` (7. sor) |
| – | `Symfony\Component\HttpFoundation\Session\SessionInterface` alias | `RequestSubscriber` → `RequestStack::getSession()` |

A teljes listát a phpunit kimenet végén lévő „Remaining … deprecation notices” blokk is mutatja.

### Csomagonkénti teendők

| Csomag | Most | Teendő |
|---|---|---|
| `api-platform/core` | 2.7.18 | kiváltás (fent), vagy 3.4/4.x a Symfony 6.4 után |
| `easycorp/easyadmin-bundle` | 3.5.23 (utolsó 3.x) | **4.x**: Symfony 5.4/6/7 alatt is megy, ezért a Symfony-váltás *előtt* érdemes. Érinti: `src/Controller/Admin/*`, `src/EventSubscriber/EasyAdminSubscriber.php`, `templates/admin/*`. PHP 8.4 deprecationöket és `NumberFormatter::TYPE_CURRENCY` deprecationt dob |
| `symfony/security-guard` | abandoned | `src/Security/LoginFormAuthenticator.php` Guard-alapú, de **nincs bekötve** (`security.yaml`-ban kikommentezve; `json_login` + `GoogleAuthenticator` működik). Valószínűleg törölhető, előtte nézd meg, hogy semmi nem hivatkozik rá |
| `symfony/web-server-bundle` | 4.4, abandoned | törölni (`config/bundles.php`, `require-dev`); a Docker/nginx kiszolgál |
| `doctrine/annotations` | abandoned | entitások (16 fájl) és route-ok annotáció → PHP attribútum (`config/packages/doctrine.yaml` `type: attribute`, `config/routes/annotations.yaml`). A Rector `AnnotationToAttribute` szabálya segít |
| `doctrine/cache`, `doctrine/common` | abandoned | a DoctrineProvider-javítással együtt kiesnek |
| `composer/package-versions-deprecated` | abandoned, fix `1.11.99.5` | eltávolítható, ha semmi nem igényli (`composer why`) |
| `symfony/proxy-manager-bridge` | 5.4 | 7.x-ben megszűnik (lazy ghost objects) |
| `doctrine/orm` | 2.20 | 3.x csak később; a 2.20 megy Symfony 6.4/7.4-gyel |
| `phpunit/phpunit` | 9.6 | később 10/11; a `symfony/phpunit-bridge`-dzsel együtt |
| `symfony/flex` | 1.22 | 2.x |
| `league/oauth2-google` | 4.2 | 5.x opcionális |
| `symfony/maker-bundle` | 1.50 (dev) | a Symfony-verzióval együtt léptetni |

## Buktatók, amikre figyelni kell

1. **Symfony Flex és a hiányzó `symfony.lock`**: a repóban nincs `symfony.lock`, ezért egy `composer update` közben a Flex az összes receptet újnak tekintheti, és fájlokat hoz létre/módosít: a `docker-compose.yml`-be és a `docker-compose.override.yml`-be egy Postgres `database` service-t ír, `config/packages/*.yaml`, `migrations/`, `assets/app.js` és `templates/base.html.twig` változások jönnek. Minden composer parancs után nézd át a `git status`-t, és ami nem kell, állítsd vissza. Érdemes egyszer egy ilyen futás után **csak a `symfony.lock`-ot megtartani és commitolni** (a recept-fájlokat visszaállítva), mert az jelzi a Flexnek, hogy a receptek már telepítve vannak.
2. **Composer 2.11 advisory blocking**: sebezhető verziót nem telepít, csak `config.policy.advisories.ignore`/`ignore-id` kivétellel. A kivétel szándékos döntés legyen, ne automatikus.
3. **Cache**: csomagfrissítés után minden környezet cache-ét törölni kell, különben a régi vendor útvonalak miatt elhasal (`rm -rf var/cache/{dev,test,e2e,prod}`).
4. **Saját, nem commitolt helyi változások**: a `.env` (`f1tippelek` DB) és a `docker-compose.yml` `MYSQL_DATABASE` sora szándékosan nincs commitolva, és nem is kell bevinni. Commitnál ezeket ki kell hagyni.

## Munkamódszer, ami bevált

```bash
# a php konténerben (root), a projekt könyvtárában:
docker compose exec -T -w /var/www/f1tipp_sf4 -e COMPOSER_ALLOW_SUPERUSER=1 php composer <parancs>

# tesztek
docker compose exec -T -w /var/www/f1tipp_sf4 php su www-data -s /bin/sh -c "php bin/phpunit"
npm run e2e

# logok: futás előtt ürítés, utána összevetés
docker compose exec -T -w /var/www/f1tipp_sf4 php sh -c ': > var/log/e2e.log'
#   -> e2e után: var/log/e2e.log (ERROR/WARNING + "Deprecated" csoportosítva),
#      valamint `docker compose logs php` (a php.ini miatt a nyers PHP hibák ide mennek)
```

Érdemes minden nagyobb lépés előtt felvenni a baseline-t (phpunit, e2e, a log-üzenetek csoportosítva), és utána összevetni.

## Nem verziófüggő, ismert hibák (külön döntés kell róluk)

- `src/Controller/Page/Admin/ResetSeason.php`: halott legacy kód, nem létező `$this->registry` / `$this->entityManager` property-kre hivatkozik, és nincs route-hoz kötve.
- `php bin/console lint:container`: az `app.controller.cron.feed_controller` service-nél string argumentum szerepel `@` service-hivatkozás helyett (`config/services.yaml` 45. sor).
- Production: a tárhelyen PHP 8.4 kell (`pdo_mysql`, `intl`, `zip`) és `display_errors=Off`, különben a vendor-deprecationök a válaszba kerülhetnek.
