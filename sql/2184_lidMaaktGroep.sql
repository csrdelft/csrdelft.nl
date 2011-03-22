ALTER TABLE `groep` ADD `makeruid` VARCHAR( 4 ) NOT NULL COMMENT 'maker of beheerder van groep' AFTER `lidIsMod`;
ALTER TABLE `groeptype` ADD  `groepenAanmaakbaar` VARCHAR( 50 ) NOT NULL DEFAULT  'P_LEDEN_MOD' COMMENT 'permissie(s) voor aanmaken van groepen' AFTER `syncWithLDAP`;
