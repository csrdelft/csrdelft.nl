<?php

use Phinx\Migration\AbstractMigration;

/**
 * Door het runnen van deze migratie denkt DoctrineMigrations dat de eerste migratie al gerund is.
 */
class NaarDoctrineMigrationsMigratie extends AbstractMigration {
	public function up() {
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
		$this->query('INSERT INTO `migration_versions` (`version`, `executed_at`) VALUES (\'20200705132858\', \'2020-07-05 13:30:40\');');
	}

	public function down() {
		$this->query('DROP TABLE `migration_versions`;');
	}
}
