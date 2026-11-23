-- Nyeremény modul (helyezés => összeg, megjegyzés, "Még nyitott a nevezés"), az admin "Nyeremény" menüjében szerkeszthető.
-- Élesben egyszer lefuttatandó a kód feltöltése ELŐTT; a kezdő értékek a korábban a sablonba égetett értékek.
-- A prize_draw egyetlen sora (id = 1) kötelező: a kód ezt olvassa (PrizeDraw::ID).
-- A megjegyzés ékezetes: a kapcsolat kódolása legyen utf8mb4, különben duplán kódolva kerül be.
SET NAMES utf8mb4;

CREATE TABLE `prize_draw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_open` tinyint(1) NOT NULL DEFAULT '0',
  `note` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_draw_id` int(11) NOT NULL,
  `place` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_PRIZE_DRAW_PLACE` (`prize_draw_id`, `place`),
  KEY `IDX_51C88BC1DF58A3C6` (`prize_draw_id`),
  CONSTRAINT `FK_51C88BC1DF58A3C6` FOREIGN KEY (`prize_draw_id`) REFERENCES `prize_draw` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `prize_draw` (`id`, `entry_open`, `note`) VALUES
  (1, 0, '12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000');

INSERT INTO `prize` (`prize_draw_id`, `place`, `amount`) VALUES
  (1, 1, 24000),
  (1, 2, 14400),
  (1, 3, 9600);
