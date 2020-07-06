<?php

use Phinx\Migration\AbstractMigration;

class FixDoctrineMigrationsMigratie extends AbstractMigration {
	public function up() {
		$this->query('DROP TABLE IF EXISTS migration_versions;');
		$this->query('DROP TABLE IF EXISTS doctrine_migration_versions');
		$this->query(<<<SQL
CREATE TABLE `doctrine_migration_versions` (
	`version` VARCHAR(191) NOT NULL COLLATE 'utf8_unicode_ci',
	`executed_at` DATETIME NULL DEFAULT NULL,
	`execution_time` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`version`) USING BTREE
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
SQL
		);
		$this->query(<<<SQL
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\\\Version20200705132858', NULL, NULL);
SQL
		);
	}

	public function down() {
		$this->query('DROP TABLE IF EXISTS migration_versions;');
		$this->query('DROP TABLE IF EXISTS doctrine_migration_versions');
		$this->query(<<<SQL
CREATE TABLE `migration_versions` (
	`version` VARCHAR(14) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`executed_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
	PRIMARY KEY (`version`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
		);
		$this->query(<<<SQL
INSERT INTO `migration_versions` (`version`, `executed_at`) VALUES ('20200705132858', '2020-07-05 13:30:40');
SQL
		);
	}
}
