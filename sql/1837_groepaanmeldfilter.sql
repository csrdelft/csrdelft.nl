ALTER TABLE `groep` ADD `aanmeldbaar_` VARCHAR( 50 ) NOT NULL COMMENT 'permissie(s) voor aanmelden' AFTER `aanmeldbaar`;
UPDATE groep SET aanmeldbaar_ = 'P_LOGGED_IN' WHERE aanmeldbaar =1;
ALTER TABLE `groep` DROP `aanmeldbaar`;
ALTER TABLE `groep` CHANGE `aanmeldbaar_` `aanmeldbaar` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'permissie(s) voor aanmelden';
