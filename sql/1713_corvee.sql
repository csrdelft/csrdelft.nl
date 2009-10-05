ALTER TABLE `lid` ADD `corvee_punten_bonus` INT( 11 ) NOT NULL AFTER `corvee_punten`;

ALTER TABLE `maaltijd` ADD `type` ENUM( "normaal", "corvee" ) NOT NULL AFTER `datum` ;

ALTER TABLE `maaltijd` ADD `schoonmaken_frituur` INT( 11 ) NOT NULL ,
ADD `schoonmaken_afzuigkap` INT( 11 ) NOT NULL ,
ADD `schoonmaken_keuken` INT( 11 ) NOT NULL ;

ALTER TABLE `maaltijd` ADD `punten_schoonmaken_frituur` SMALLINT( 4 ) NOT NULL ,
ADD `punten_schoonmaken_afzuigkap` SMALLINT( 4 ) NOT NULL ,
ADD `punten_schoonmaken_keuken` SMALLINT( 4 ) NOT NULL ;

ALTER TABLE `maaltijdcorvee` ADD `schoonmaken_frituur` TINYINT( 1 ) NOT NULL AFTER `theedoek` ,
ADD `schoonmaken_afzuigkap` TINYINT( 1 ) NOT NULL AFTER `schoonmaken_frituur` ,
ADD `schoonmaken_keuken` TINYINT( 1 ) NOT NULL AFTER `schoonmaken_afzuigkap` ;