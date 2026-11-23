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
