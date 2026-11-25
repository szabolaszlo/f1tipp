-- ÉLESEN MÉG NEM FUTTATOTT SÉMAVÁLTOZÁSOK, egyben, a futtatási sorrendben.
-- A kód feltöltése ELŐTT, egyszer kell lefuttatni az éles adatbázison (a feltöltött kód már az új táblákat várja).
-- Lefuttatás után: a fájl átnevezése docs/sql/applied/<éles futtatás dátuma>.sql-re; az új változások egy új pending.sql-be gyűlnek.
-- A szakaszok ékezetes adatot is tartalmaznak: a kapcsolat kódolása utf8mb4 legyen (a SET NAMES ezt beállítja).
SET NAMES utf8mb4;

-- ============================================================================
-- 1. Bajnoki pontozási tábla (extract-static-points branch, 2026-11-23)
-- ============================================================================
-- Bajnoki pontozási tábla (hétvégi helyezés => bajnoki pont), az admin "Bajnoki pontok" menüjében szerkeszthető.
-- Élesben egyszer lefuttatandó a kód feltöltése ELŐTT; a kezdő értékek a korábban kódba égetett tábla.
CREATE TABLE `championship_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `place` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CHAMPIONSHIP_POINT_PLACE` (`place`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `championship_point` (`place`, `points`) VALUES
  (1, 15),
  (2, 11),
  (3, 9),
  (4, 7),
  (5, 6),
  (6, 5),
  (7, 4),
  (8, 2),
  (9, 1),
  (10, 0);

-- ============================================================================
-- 2. Nyeremény modul (extract-static-points branch, 2026-11-23)
-- ============================================================================
-- Nyeremény modul (helyezés => összeg, megjegyzés, "Még nyitott a nevezés"), az admin "Nyeremény" menüjében szerkeszthető.
-- Élesben egyszer lefuttatandó a kód feltöltése ELŐTT; a kezdő értékek a korábban a sablonba égetett értékek.
-- A prize_draw egyetlen sora (id = 1) kötelező: a kód ezt olvassa (PrizeDraw::ID).

CREATE TABLE `prize_draw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_open` tinyint(1) NOT NULL DEFAULT '0',
  `note` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A helyezés egyediségét az admin validátora őrzi, nem egyedi index (lásd src/Entity/Prize.php).
CREATE TABLE `prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_draw_id` int(11) NOT NULL,
  `place` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_51C88BC1DF58A3C6` (`prize_draw_id`),
  CONSTRAINT `FK_51C88BC1DF58A3C6` FOREIGN KEY (`prize_draw_id`) REFERENCES `prize_draw` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `prize_draw` (`id`, `entry_open`, `note`) VALUES
  (1, 0, '12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000');

INSERT INTO `prize` (`prize_draw_id`, `place`, `amount`) VALUES
  (1, 1, 24000),
  (1, 2, 14400),
  (1, 3, 9600);

-- ============================================================================
-- 3. Archív szezonok képei (extract-static-points branch, 2026-11-23)
-- ============================================================================
-- Az Eredmények menü korábbi évei az information tábla elmentett HTML-jei (slug = év), fix kép-URL-ekkel.
-- A 2017-2018-as (régi keretrendszer) és a 2019-2021-es (régi Encore-hash) kupaképek 404-et adnak, a 2022-es
-- kupák és a 2022-2025-ös kockás zászló URL-je hash-függő, a következő hashváltozással eltörne.
-- Mind a hash nélkül kimásolt fájlokra mutat ezután: build/images/trophy/trophy_*.svg és build/images/checkered-flag.png.
-- Újrafuttatható (a lecserélt URL-ek már nem illeszkednek). Utána a prod cache-t üríteni kell (a 2LC tárolja az information sorokat).

UPDATE `information` SET `content` = REPLACE(REPLACE(REPLACE(`content`,
    '/src/view/image/trophy_gold.png?v=1502019288', '/build/images/trophy/trophy_gold.svg'),
    '/build/images/trophy_gold.c61f05af.png', '/build/images/trophy/trophy_gold.svg'),
    '/build/images/trophy_gold.8220f47f.png', '/build/images/trophy/trophy_gold.svg')
WHERE `content` LIKE '%trophy\_gold%';

UPDATE `information` SET `content` = REPLACE(REPLACE(REPLACE(`content`,
    '/src/view/image/trophy_silver.png?v=1502019288', '/build/images/trophy/trophy_silver.svg'),
    '/build/images/trophy_silver.9ef4b3af.png', '/build/images/trophy/trophy_silver.svg'),
    '/build/images/trophy_silver.a8f2bad3.png', '/build/images/trophy/trophy_silver.svg')
WHERE `content` LIKE '%trophy\_silver%';

UPDATE `information` SET `content` = REPLACE(REPLACE(REPLACE(`content`,
    '/src/view/image/trophy_bronze.png?v=1502019288', '/build/images/trophy/trophy_bronze.svg'),
    '/build/images/trophy_bronze.209ef2de.png', '/build/images/trophy/trophy_bronze.svg'),
    '/build/images/trophy_bronze.32870810.png', '/build/images/trophy/trophy_bronze.svg')
WHERE `content` LIKE '%trophy\_bronze%';

UPDATE `information` SET `content` = REPLACE(`content`,
    '/build/images/checkered-flag.6fb7dbeb.png', '/build/images/checkered-flag.png')
WHERE `content` LIKE '%checkered-flag.6fb7dbeb.png%';

-- Ellenőrzés: üres eredményt kell adnia (ha mégis ad sort, abban egy itt nem ismert régi kép-URL maradt).
SELECT `slug` FROM `information`
WHERE `content` LIKE '%/src/view/image/%'
   OR `content` REGEXP 'build/images/(trophy_[a-z]+|checkered-flag)\\.[0-9a-f]{8}\\.png';

-- ============================================================================
-- 4. Szabályzat új szövege (extract-static-points branch, 2026-11-23)
-- ============================================================================
-- Az information 23-as sora (Szabályzat menü) tömörebb, a mostani pontozáshoz igazított szöveget kap (a régi
-- létszámarányos pontozás és az elavult példaképek kikerültek). Fix szélességek nélkül, a mobilos megjelenést
-- a .f1-prose stílus adja. Utána az adminban szerkeszthető marad. Ha élesben a futtatás előtt az adminban
-- módosítják a szabályzatot, ez felülírja. Utána a prod cache-t üríteni kell (2LC).

UPDATE `information` SET `content` = '<p><strong>Röviden:</strong> minden versenyhétvégén megtippeled az időmérő, a sprint és a futam sorrendjét. A találataidért hétvégi pontokat kapsz. A hétvégi pontok sorrendje alapján jár a bajnoki pont, és az éves bajnokságot a legtöbb bajnoki pontot gyűjtő tippelő nyeri.</p>

<h3>Mire tippelsz?</h3>

<table>
	<thead>
		<tr>
			<th>Esemény</th>
			<th>Telitalálat</th>
			<th>Mellétalálat</th>
			<th>Max.</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Időmérő (első 10)</td>
			<td>4</td>
			<td>1</td>
			<td>40</td>
		</tr>
		<tr>
			<td>Sprint (első 8)</td>
			<td>1</td>
			<td>0</td>
			<td>8</td>
		</tr>
		<tr>
			<td>Futam (első 10)</td>
			<td>3</td>
			<td>1</td>
			<td>30</td>
		</tr>
		<tr>
			<td>Safety Car (igen/nem)</td>
			<td>3</td>
			<td>0</td>
			<td>3</td>
		</tr>
	</tbody>
</table>

<ul>
	<li><strong>Telitalálat:</strong> a versenyző pontosan azon a helyen végzett, ahová tippelted.</li>
	<li><strong>Mellétalálat:</strong> a versenyző bekerült a tippelt első 10-be, csak más helyen.</li>
	<li>Tippelni az adott esemény kezdetéig lehet.</li>
	<li>A hivatalos végeredmény számít, nem a rajtsorrend. Ha utólag változik (pl. büntetés miatt), nálunk is változik. Forrás: <a href="https://www.formula1.com/en/results.html">formula1.com</a>.</li>
</ul>

<h3>Safety Car</h3>

<p>Arra tippelsz, hogy a futamon kiküldik-e a biztonsági autót. Hogy hányszor, az mindegy. Csak a valódi Safety Car számít, a virtuális (VSC) nem.</p>

<ul>
	<li><strong>Számít:</strong> ha a Safety Car mögött mennek és számolódnak a körök, vagy ha a futam a Safety Car mögül indul.</li>
	<li><strong>Nem számít:</strong> ha piros zászlós megszakítás alatt, csak pályaellenőrzésre küldik ki (pl. nagy esőben).</li>
</ul>

<h3>Hétvégi és bajnoki pontok</h3>

<p>A hétvége végén összeadjuk mindenki hétvégi pontjait. Ebből sorrend lesz, és a helyezésed (felső sor) alapján kapsz bajnoki pontot (alsó sor):</p>

<table>
	<thead>
		<tr>
			<th style="text-align:center">1.</th>
			<th>2.</th>
			<th>3.</th>
			<th>4.</th>
			<th>5.</th>
			<th>6.</th>
			<th>7.</th>
			<th>8.</th>
			<th>9.</th>
			<th>10.</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="text-align:center">15</td>
			<td>11</td>
			<td>9</td>
			<td>7</td>
			<td>6</td>
			<td>5</td>
			<td>4</td>
			<td>2</td>
			<td>1</td>
			<td>0</td>
		</tr>
	</tbody>
</table>

<ul>
	<li>Az éves bajnokságba csak a bajnoki pontok számítanak. A hétvégi pontok csak a hétvégi sorrendet döntik el.</li>
	<li>A 10. helytől hátrébb nem jár bajnoki pont, és 0 hétvégi ponttal sem.</li>
	<li><strong>Holtverseny:</strong> azonos hétvégi pontszám azonos helyezést ér, a következő helyezések kimaradnak. Pl. ha ketten holtversenyben 3.-ok, mindketten 9 pontot kapnak, az utánuk következő tippelő pedig 5., és 6 pontot kap.</li>
</ul>

<h3>Kupák</h3>

<p>A hétvége legtöbb hétvégi pontját gyűjtő tippelő arany, a második ezüst, a harmadik bronz kupát kap. Holtversenyben mindenki megkapja ugyanazt a kupát, és a következő pontszám kapja a következő kupát.</p>

<h3>Bajnokság és nyeremény</h3>

<ul>
	<li>Pontegyenlőségnél a több arany kupa dönt, utána az ezüst, végül a bronz kupák száma.</li>
	<li>Ha a pontok és a kupák is egyeznek, a holtversenyben lévők összeadják az általuk elfoglalt helyek nyereményét, és egyenlően elosztják. Pl. ha az első 3 hely díjazott, és hárman holtversenyben elsők, a három díj összegét harmadolják, a következő tippelő (valójában 4.) már nem kap díjat.</li>
</ul>'
WHERE `id` = 23;
