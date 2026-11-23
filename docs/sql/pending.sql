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
