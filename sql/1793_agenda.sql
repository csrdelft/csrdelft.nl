CREATE TABLE IF NOT EXISTS `agenda` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titel` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `beschrijving` text COLLATE utf8_general_ci NOT NULL,
  `begin` datetime NOT NULL,
  `eind` datetime NOT NULL,
  `rechtenBekijken` varchar(200) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci  ;
