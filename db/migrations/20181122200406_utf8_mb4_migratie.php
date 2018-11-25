<?php

use Phinx\Migration\AbstractMigration;

class Utf8Mb4Migratie extends AbstractMigration
{
    public function up() {
    	$this->query(<<<'SQL'
-- Kan niet de charset van een tabel aanpassen als er foreign keys zijn.
SET FOREIGN_KEY_CHECKS = 0;

ALTER DATABASE `csrdelft`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_general_ci;

-- Maximale lengte van een key in InnoDB utf8mb4 is 191 tekens.
-- De volgende velden zijn primary/foreign/unique key en hebben type T::StringKey gerkegen.
ALTER TABLE `accounts`
	CHANGE COLUMN `username` `username` VARCHAR(191) NOT NULL AFTER `uid`;

ALTER TABLE `acl`
	CHANGE COLUMN `environment` `environment` VARCHAR(191) NOT NULL FIRST,
	CHANGE COLUMN `action` `action` VARCHAR(191) NOT NULL AFTER `environment`,
	CHANGE COLUMN `resource` `resource` VARCHAR(191) NOT NULL AFTER `action`;

ALTER TABLE `agenda_verbergen`
	CHANGE COLUMN `refuuid` `refuuid` VARCHAR(191) NOT NULL AFTER `uid`;

ALTER TABLE `biebbeschrijving`
	CHANGE COLUMN `schrijver_uid` `schrijver_uid` VARCHAR(191) NOT NULL AFTER `boek_id`;

ALTER TABLE `biebexemplaar`
	CHANGE COLUMN `eigenaar_uid` `eigenaar_uid` VARCHAR(191) NOT NULL AFTER `boek_id`,
	CHANGE COLUMN `uitgeleend_uid` `uitgeleend_uid` VARCHAR(191) NULL DEFAULT NULL AFTER `opmerking`;

ALTER TABLE `cms_paginas`
	CHANGE COLUMN `naam` `naam` VARCHAR(191) NOT NULL FIRST;

ALTER TABLE `execution_times`
	CHANGE COLUMN `request` `request` VARCHAR(191) NOT NULL FIRST;

ALTER TABLE `fotoalbums`
	CHANGE COLUMN `subdir` `subdir` VARCHAR(191) NOT NULL FIRST;

ALTER TABLE `fotos`
	CHANGE COLUMN `subdir` `subdir` VARCHAR(191) NOT NULL FIRST,
	CHANGE COLUMN `filename` `filename` VARCHAR(191) NOT NULL AFTER `subdir`;

ALTER TABLE `foto_tags`
	CHANGE COLUMN `refuuid` `refuuid` VARCHAR(191) NOT NULL FIRST,
	CHANGE COLUMN `keyword` `keyword` VARCHAR(191) NOT NULL AFTER `refuuid`;

ALTER TABLE `instellingen`
	CHANGE COLUMN `module` `module` VARCHAR(191) NOT NULL FIRST,
	CHANGE COLUMN `instelling_id` `instelling_id` VARCHAR(191) NOT NULL AFTER `module`;

ALTER TABLE `lidinstellingen`
	CHANGE COLUMN `module` `module` VARCHAR(191) NOT NULL AFTER `uid`,
	CHANGE COLUMN `instelling_id` `instelling_id` VARCHAR(191) NOT NULL AFTER `module`;

ALTER TABLE `lidtoestemmingen`
	CHANGE COLUMN `module` `module` VARCHAR(191) NOT NULL AFTER `uid`,
	CHANGE COLUMN `instelling_id` `instelling_id` VARCHAR(191) NOT NULL AFTER `module`;

ALTER TABLE `login_sessions`
	CHANGE COLUMN `session_hash` `session_hash` VARCHAR(191) NOT NULL FIRST;

ALTER TABLE `onetime_tokens`
	CHANGE COLUMN `url` `url` VARCHAR(191) NOT NULL AFTER `uid`,
	CHANGE COLUMN `token` `token` VARCHAR(191) NOT NULL AFTER `url`;

ALTER TABLE `verticalen`
	CHANGE COLUMN `naam` `naam` VARCHAR(191) NOT NULL AFTER `letter`,
	CHANGE COLUMN `familie` `familie` VARCHAR(191) NOT NULL AFTER `naam`;

ALTER TABLE `onderverenigingen`
	CHANGE COLUMN `naam` `naam` VARCHAR(191) NOT NULL AFTER `id`,
	CHANGE COLUMN `familie` `familie` VARCHAR(191) NOT NULL AFTER `naam`;

ALTER TABLE `groepen`
	CHANGE COLUMN `naam` `naam` VARCHAR(191) NOT NULL AFTER `id`,
	CHANGE COLUMN `familie` `familie` VARCHAR(191) NOT NULL AFTER `naam`;

ALTER TABLE `commissies`
	CHANGE COLUMN `naam` `naam` VARCHAR(191) NOT NULL AFTER `id`,
	CHANGE COLUMN `familie` `familie` VARCHAR(191) NOT NULL AFTER `naam`;

-- Loop alle tabellen af en converteer ze naar InnoDB, utf8mb4 met 	collationnering utf8mb4_general_ci
-- InnoDB zou performant genoeg moeten zijn voor al onze doeleinden.
-- Origineel in MyISAM: Document, forum_draden, forum_posts, mededelingen, saldolog (deprecated)
ALTER TABLE `accounts` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `acl` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `activiteiten` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `activiteit_deelnemers` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `agenda` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `agenda_verbergen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `besturen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `bestuurs_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `bewoners` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `biebauteur` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `biebbeschrijving` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `biebboek` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `biebcategorie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `biebexemplaar` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `bijbelrooster` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `bijbelrooster_old` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `changelog` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviBestelling` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviBestellingInhoud` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviCategorie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviLog` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviPrijs` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviProduct` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `CiviSaldo` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `cms_paginas` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `commissies` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `commissie_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `courant` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `courantbericht` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `courantcache` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `crv_functies` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `crv_kwalificaties` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `crv_repetities` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `crv_taken` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `crv_voorkeuren` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `crv_vrijstellingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `debug_log` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `Document` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `DocumentCategorie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `eetplan` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `eetplan_bekenden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `execution_times` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_categorien` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_delen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_draden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_draden_gelezen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_draden_reageren` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_draden_verbergen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_draden_volgen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `forum_posts` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `fotoalbums` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `fotos` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `foto_tags` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `geolocations` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `gesprekken` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `gesprek_berichten` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `gesprek_deelnemers` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `GoogleToken` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `groep` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `groepen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `groeptype` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `groep_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `instellingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `ketzers` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `ketzer_deelnemers` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `kringen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `kring_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `lichtingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `lichting_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `lidinstellingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `lidtoestemmingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `log` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `logAggregated` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `login_remember` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `login_sessions` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mededelingcategorie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mededelingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `memory_scores` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `menus` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mlt_aanmeldingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mlt_abonnementen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mlt_archief` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mlt_beoordelingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mlt_maaltijden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `mlt_repetities` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `onderverenigingen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `ondervereniging_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `onetime_tokens` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `peiling` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `peiling_optie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `peiling_stemmen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `phinxlog` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `pin_transacties` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `pin_transactie_match` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `profielen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `saldolog` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `savedquery` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCieBestelling` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCieBestellingInhoud` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCieGrootboekType` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCieKlanten` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCieLog` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCiePrijs` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `socCieProduct` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `verticalen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `verticale_leden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `voorkeurCommissie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `voorkeurCommissieCategorie` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `voorkeurOpmerking` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `voorkeurVoorkeur` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `werkgroepen` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `werkgroep_deelnemers` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';
ALTER TABLE `woonoorden` ENGINE InnoDB, CONVERT TO CHARSET utf8mb4 COLLATE 'utf8mb4_general_ci';

SET FOREIGN_KEY_CHECKS = 1;
SQL
);
		}

		public function down() {
    	// Sorry
		}
}
