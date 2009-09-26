ALTER TABLE `mededelingcategorie` CHANGE `rank` `prioriteit` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '255' COMMENT 'De volgorde van de categorieën in de dropdown'; 
ALTER TABLE `mededeling`
	CHANGE `datum` `datum` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	CHANGE `rank` `prioriteit` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '255' COMMENT 'Hoe belangrijk is deze mededeling?';
ALTER TABLE `mededeling`
	DROP `verborgen`,
	DROP `verwijderd`;
ALTER TABLE `mededeling` ADD `zichtbaarheid` ENUM( 'wacht_goedkeuring', 'zichtbaar', 'onzichtbaar', 'verwijderd' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'zichtbaar' COMMENT 'Is hij zichtbaar?' AFTER `prive` ;