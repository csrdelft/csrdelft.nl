<?php

use Phinx\Migration\AbstractMigration;

class TagTypeMigratie extends AbstractMigration {
	/**
	 * Zet tags goed op custom velden.
	 */
	public function up() {
		$this->query('ALTER TABLE accounts CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE username username VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE acl CHANGE environment environment VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE action action VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE resource resource VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE activiteit_deelnemers CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE activiteiten CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE activiteiten CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE agenda_verbergen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE refuuid refuuid VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE besturen CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE besturen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE bestuurs_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE bewoners CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE changelog CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE CiviBestelling CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE CiviSaldo CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE cms_paginas CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE commissie_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE commissies CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE commissies CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE courant CHANGE verzender verzender VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE courantbericht CHANGE cat cat ENUM(\'voorwoord\', \'bestuur\', \'csr\', \'overig\', \'sponsor\') NOT NULL COMMENT \'(DC2Type:enumCourantCategorie)\', CHANGE volgorde volgorde INT NOT NULL, CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE crv_kwalificaties CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE crv_taken CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE crv_voorkeuren CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE crv_vrijstellingen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE begin_datum begin_datum DATETIME NOT NULL, CHANGE eind_datum eind_datum DATETIME NOT NULL');
		$this->query('ALTER TABLE debug_log CHANGE dump dump LONGTEXT COMMENT \'(DC2Type:longtext)\' NOT NULL, CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, CHANGE su_uid su_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE Document CHANGE eigenaar eigenaar VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE eetplan CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE eetplan_bekenden CHANGE uid1 uid1 VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE uid2 uid2 VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE forum_delen_meldingen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE forum_draden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE laatste_wijziging_uid laatste_wijziging_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE forum_draden_gelezen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE forum_draden_reageren CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE forum_draden_verbergen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE forum_draden_volgen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE niveau niveau ENUM(\'nooit\', \'vermelding\', \'altijd\') NOT NULL COMMENT \'(DC2Type:enumForumDraadMeldingNiveau)\'');
		$this->query('ALTER TABLE forum_posts CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE forumplaatjes CHANGE access_key access_key VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE maker maker VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE foto_tags CHANGE refuuid refuuid VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE keyword keyword VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE door door VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE fotoalbums CHANGE subdir subdir VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE owner owner VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE fotos CHANGE subdir subdir VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE filename filename VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE owner owner VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE GoogleToken CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE groep_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE groepen CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE groepen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE instellingen CHANGE module module VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE instelling_id instelling_id VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE ketzer_deelnemers CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE ketzers CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE ketzers CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE kring_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE kringen CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE kringen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE lichting_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE lichtingen CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') COMMENT \'(DC2Type:enumGroepStatus)\' NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL, CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE lichtingen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE lidinstellingen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE module module VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE instelling_id instelling_id VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE lidtoestemmingen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE module module VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE instelling_id instelling_id VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE log CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE login_remember CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE login_sessions CHANGE session_hash session_hash VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE memory_scores CHANGE groep groep VARCHAR(255) NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE mlt_aanmeldingen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE mlt_abonnementen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE mlt_beoordelingen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE ondervereniging_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE onderverenigingen CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE onderverenigingen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE onetime_tokens CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE url url VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE token token VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL');
		$this->query('ALTER TABLE peiling CHANGE eigenaar eigenaar VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE peiling_optie CHANGE ingebracht_door ingebracht_door VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL');
		$this->query('ALTER TABLE peiling_stemmen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE profielen CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE echtgenoot echtgenoot VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, CHANGE patroon patroon VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, CHANGE changelog changelog TEXT COMMENT \'(DC2Type:changelog)\' NOT NULL');
		$this->query('ALTER TABLE verticale_leden CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE verticalen CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE verticalen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE voorkeurOpmerking CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->query('ALTER TABLE voorkeurVoorkeur CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE timestamp timestamp DATETIME NOT NULL');
		$this->query('ALTER TABLE werkgroep_deelnemers CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE door_uid door_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE opmerking2 opmerking2 TEXT COMMENT \'(DC2Type:groepkeuzeselectie)\' DEFAULT NULL');
		$this->query('ALTER TABLE werkgroepen CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE werkgroepen CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
		$this->query('ALTER TABLE woonoorden CHANGE maker_uid maker_uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE naam naam VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE familie familie VARCHAR(191) COMMENT \'(DC2Type:stringkey)\' NOT NULL, CHANGE status status ENUM(\'ft\', \'ht\', \'ot\') NOT NULL COMMENT \'(DC2Type:enumGroepStatus)\', CHANGE soort soort ENUM(\'w\', \'h\') COMMENT \'(DC2Type:enumHuisStatus)\' NOT NULL, CHANGE keuzelijst2 keuzelijst2 TEXT COMMENT \'(DC2Type:groepkeuze)\' DEFAULT NULL');
		$this->query('ALTER TABLE woonoorden CHANGE versie versie ENUM(\'v1\', \'v2\') COMMENT \'(DC2Type:enumGroepVersie)\' NOT NULL');
	}

	public function down() {
		// niets
	}
}
