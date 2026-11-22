# Symfony 6.4 upgrade: állapot és következő lépések

Átadó jegyzet a `php84symfony64` branch után (2026-11-16). Az előző verzió (a `php84` branch végén)
a git historyban megvan: `git show cc3a8e7:docs/symfony-upgrade-next-steps.md`.

## Jelenlegi állapot

- PHP 8.4, **Symfony 6.4 LTS**, Doctrine ORM 2.20 (PHP attribútum mapping), EasyAdmin 4.29, Twig 3.
- Frontend: Webpack Encore 6 (webpack 5), Vue 3.5, vue-i18n 11 (composition mód), axios 1.x.
- Tesztek: `php bin/phpunit` → 38 OK, 0 deprecation; `npm run e2e` → 28 passed, 1 skipped.
- `composer audit`: **nincs advisory**. Elhagyott csomag: `doctrine/cache`, `doctrine/common` (az ORM 2.x igényli).
- `npm audit`: 1 moderate maradt, a Bootstrap 3 (lásd lent).

### Mi változott a branch-en (commitonként)

1. **API Platform kiváltása**: a 3 csak olvasó végpontot (`/api/events`, `/api/feeds`, `/api/information/{id}`)
   a `src/Controller/Api/ContentApiController.php` szolgálja ki, ugyanazzal a JSON-formátummal
   (teszt: `tests/Integration/Controller/ContentApiTest.php`). A `symfony.lock` commitolva van.
2. **5.4-es deprecationök**: Doctrine cache PSR-6 poolokkal (az app kódja a `App\Cache\ResultCache`
   wrapperen át éri el a result cache-t, a régi `save()` élettartam-szemantikával), session storage factory,
   `password_hashers`, `PasswordAuthenticatedUserInterface`, `getUserIdentifier()`, a nem használt Guard
   `LoginFormAuthenticator` törölve, `ManagerRegistry` injektálás `getDoctrine()` helyett, flash bag a `RequestStack`-ből.
3. `symfony/web-server-bundle` és `composer/package-versions-deprecated` eltávolítva.
4. Annotációk → PHP attribútumok (Doctrine, Gedmo, route-ok), `doctrine/annotations` kikerült.
5. EasyAdmin 3.5 → 4.29.
6. Symfony 5.4 → 6.4.
7. Frontend csomagok (npm audit 68 → 1).

## Deploy-teendők

- `composer install` a szokásos scriptekkel (`cache:clear`, `assets:install public`): az EasyAdmin 4 új
  assetjei a `public/bundles/easyadmin/` alá kerülnek (gitignore-olt, deploykor generálódik).
  A régi `public/bundles/apiplatform/` törölhető.
- `npm ci && npm run build` (Node 18+; helyben 22-vel tesztelve).
- Minden környezet cache-ét törölni kell (`rm -rf var/cache/*`).
- Production: PHP 8.4 (`pdo_mysql`, `intl`, `zip`) és `display_errors=Off`.

## Ami maradt (külön döntés / külön branch)

| Téma | Részletek |
|---|---|
| EasyAdmin pretty URL-ek | Az egyetlen maradt runtime deprecation (EasyAdmin 5-ben kötelező). Kell hozzá `#[AdminDashboard]` a `DashboardController`-en, `config/routes/easyadmin.yaml` (`type: easyadmin.routes`), `#[AdminRoute]` a `DriverCrudController::syncDriverPoints()`-on, a `re_calculate_points` redirect route-neve (`app_admin_dashboard_index`), és az e2e `tests/e2e/pages/AdminResultPage.js` szelektorai (most `crudAction=index` query-paramra keresnek) |
| Symfony 7.4 LTS | A kód 6.4-en deprecation-mentes, a 7.4-re lépés főleg constraint-csere + `composer update`. EasyAdmin 4.29 és DoctrineBundle 2.x támogatja |
| Doctrine ORM 3 | A `doctrine/cache` / `doctrine/common` csak így esik ki. Előtte: `ReCalculateAllPointsController` (`$stmt->execute()` → `executeStatement()`), `Repository\*` `_em` használat, `array` típusú oszlop (`User::$roles`) → `json` migrációval |
| Bootstrap 3 | `bootstrap@3.4.1` (utolsó 3.x) marad, a data-* attribútumos XSS advisory csak 4+/5-ben javított; ez UI-átírás |
| E2e-eszközök | `tests/e2e` (codeceptjs 3.7.9, a legfrissebb 3.x) tranzitív függőségei között van advisory; csak helyben fut, nem kerül deployra |
| PHPUnit 9 → 10/11 | `symfony/phpunit-bridge` 6.4-gyel |

## Nem verziófüggő, ismert hibák (változatlanul nyitva)

- `src/Controller/Page/Admin/ResetSeason.php`: halott legacy kód, nincs route-hoz kötve.
- `php bin/console lint:container`: az `app.controller.cron.feed_controller` service-nél string argumentum
  szerepel `@` service-hivatkozás helyett (`config/services.yaml`).
- Gedmo sluggable nincs bekapcsolva (`config/packages/stof_doctrine_extensions.yaml`-ban nincs
  `orm.default.sluggable: true`), ezért az `Information::$slug` nem generálódik: slug nélküli mentés
  `Column 'slug' cannot be null` hibát ad. A branch előtt is így volt.

## Munkamódszer

```bash
# composer a php konténerben (root):
docker compose exec -T -w /var/www/f1tipp_sf4 -e COMPOSER_ALLOW_SUPERUSER=1 php composer <parancs>

# tesztek
docker compose exec -T -w /var/www/f1tipp_sf4 php su www-data -s /bin/sh -c "php bin/phpunit"
npm run e2e
```

- A `symfony.lock` már commitolva van, így a Flex nem alkalmaz újra minden receptet; új csomagnál
  a recept-fájlokat (`git status`) érdemes átnézni.
- Composer `--no-scripts` futtatás után az `assets:install public`-ot kézzel kell futtatni (rootként,
  mert a `public/bundles` root tulajdonú), különben az EasyAdmin oldalak CSS/JS nélkül töltődnek be.
- A saját, nem commitolt helyi változások (`.env` `f1tippelek` DB, `docker-compose.yml` `MYSQL_DATABASE`)
  szándékosan nincsenek verziózva.
