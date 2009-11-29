ALTER TABLE `mededeling` ADD `doelgroep` ENUM( 'iedereen', '(oud)leden', 'leden' ) NOT NULL DEFAULT 'iedereen' AFTER `uid` ;
UPDATE mededeling SET doelgroep = 'leden' WHERE prive=1 ;
ALTER TABLE `mededeling` DROP `prive`;