ALTER TABLE `maaltijdgesloten` ADD `punten_toegekend` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `tijdstip` ;

ALTER TABLE `lid` CHANGE `corvee_voorkeuren` `corvee_voorkeuren` VARCHAR( 8 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '11111111';
UPDATE lid SET corvee_voorkeuren = CONCAT(corvee_voorkeuren,"111") 