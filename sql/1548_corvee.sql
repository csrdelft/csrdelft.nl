ALTER TABLE `lid` ADD `corvee_voorkeuren` VARCHAR( 5 ) NOT NULL AFTER `corvee_kwalikok` ;

ALTER TABLE `maaltijd` ADD `punten_kok` SMALLINT( 4 ) NOT NULL AFTER `theedoeken` ,
ADD `punten_afwas` SMALLINT( 4 ) NOT NULL AFTER `punten_kok` ,
ADD `punten_theedoek` SMALLINT( 4 ) NOT NULL AFTER `punten_afwas` ;

ALTER TABLE `maaltijdaanmelding` CHANGE `kookt` `kok` TINYINT( 1 ) NOT NULL ,
CHANGE `wastaf` `afwas` TINYINT( 1 ) NOT NULL ,
CHANGE `theedoeken` `theedoek` TINYINT( 1 ) NOT NULL ;

ALTER TABLE `maaltijdgesloten` ADD `kok` TINYINT( 1 ) NOT NULL AFTER `maalid` ,
ADD `afwas` TINYINT( 1 ) NOT NULL AFTER `kok` ,
ADD `theedoek` TINYINT( 1 ) NOT NULL AFTER `afwas` ;

UPDATE `lid` SET `corvee_voorkeuren` = '11110' ;
UPDATE `maaltijd` SET `punten_kok` = '4', `punten_afwas` = '5', `punten_theedoek` = '2' ;