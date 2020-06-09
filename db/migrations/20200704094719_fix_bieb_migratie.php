<?php

use Phinx\Migration\AbstractMigration;

class FixBiebMigratie extends AbstractMigration {
	public function up() {
		$this->query(<<<'SQL'
ALTER TABLE `biebexemplaar` DROP FOREIGN KEY `biebexemplaar_ibfk_2`;
ALTER TABLE `biebexemplaar` DROP FOREIGN KEY `biebexemplaar_ibfk_3`;
ALTER TABLE biebexemplaar
    CHANGE eigenaar_uid eigenaar_uid VARCHAR(4) COMMENT '(DC2Type:uid)' NOT NULL,
    CHANGE uitgeleend_uid uitgeleend_uid VARCHAR(4) COMMENT '(DC2Type:uid)' DEFAULT NULL,
    CHANGE toegevoegd toegevoegd DATETIME NOT NULL,
    CHANGE status status ENUM('beschikbaar', 'uitgeleend', 'teruggegeven', 'vermist') COMMENT '(DC2Type:enumBoekExemplaarStatus)' NOT NULL;
ALTER TABLE `biebexemplaar` ADD CONSTRAINT `biebexemplaar_ibfk_2` FOREIGN KEY (`eigenaar_uid`) REFERENCES `profielen` (`uid`);
ALTER TABLE `biebexemplaar` ADD CONSTRAINT `biebexemplaar_ibfk_3` FOREIGN KEY (`uitgeleend_uid`) REFERENCES `profielen` (`uid`);
SQL
		);
		$this->query(<<<'SQL'
ALTER TABLE `biebbeschrijving` DROP FOREIGN KEY `biebbeschrijving_ibfk_2`;
ALTER TABLE biebbeschrijving
    CHANGE schrijver_uid schrijver_uid VARCHAR(4) COMMENT '(DC2Type:uid)' NOT NULL,
    CHANGE toegevoegd toegevoegd DATETIME NOT NULL,
    CHANGE bewerkdatum bewerkdatum DATETIME NOT NULL;
ALTER TABLE `biebbeschrijving` ADD CONSTRAINT `biebbeschrijving_ibfk_2` FOREIGN KEY (`schrijver_uid`) REFERENCES `profielen` (`uid`);
SQL
		);
	}

	public function down() {
		$this->query(<<<'SQL'
ALTER TABLE `biebbeschrijving` DROP FOREIGN KEY `biebbeschrijving_ibfk_2`;
ALTER TABLE biebbeschrijving
    CHANGE schrijver_uid schrijver_uid VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`,
    CHANGE toegevoegd toegevoegd DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    CHANGE bewerkdatum bewerkdatum DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL;
ALTER TABLE `biebbeschrijving` ADD CONSTRAINT `biebbeschrijving_ibfk_2` FOREIGN KEY (`schrijver_uid`) REFERENCES `profielen` (`uid`);
SQL
		);
		$this->query(<<<'SQL'
ALTER TABLE `biebexemplaar` DROP FOREIGN KEY `biebexemplaar_ibfk_2`;
ALTER TABLE `biebexemplaar` DROP FOREIGN KEY `biebexemplaar_ibfk_3`;
ALTER TABLE biebexemplaar
    CHANGE eigenaar_uid eigenaar_uid VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`,
    CHANGE uitgeleend_uid uitgeleend_uid VARCHAR(191) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`,
    CHANGE toegevoegd toegevoegd DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    CHANGE status status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'beschikbaar' NOT NULL COLLATE `utf8mb4_general_ci`;
ALTER TABLE `biebexemplaar` ADD CONSTRAINT `biebexemplaar_ibfk_2` FOREIGN KEY (`eigenaar_uid`) REFERENCES `profielen` (`uid`);
ALTER TABLE `biebexemplaar` ADD CONSTRAINT `biebexemplaar_ibfk_3` FOREIGN KEY (`uitgeleend_uid`) REFERENCES `profielen` (`uid`);
SQL
		);
	}
}
