UPDATE `maaltijd` SET `punten_afwas` = '3' ;

ALTER TABLE `maaltijdaanmelding` DROP `kok` ,
DROP `afwas` ,
DROP `theedoek` ;

ALTER TABLE `maaltijdgesloten` DROP `kok` ,
DROP `afwas` ,
DROP `theedoek` ;

CREATE TABLE IF NOT EXISTS `maaltijdcorvee` (
  `maalid` int(11) NOT NULL,
  `uid` varchar(4) NOT NULL,
  `kok` tinyint(1) NOT NULL DEFAULT '0',
  `afwas` tinyint(1) NOT NULL DEFAULT '0',
  `theedoek` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`maalid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;