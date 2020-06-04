<?php

use Phinx\Migration\AbstractMigration;

class OpschonenMigratie extends AbstractMigration {
	public function up() {
		$this->query('UPDATE profielen SET patroon = null WHERE patroon = \'\'');
		$this->query('ALTER TABLE `forumplaatjes`
	COLLATE=\'utf8mb4_general_ci\',
	CONVERT TO CHARSET utf8mb4,
	CHANGE COLUMN `access_key` `access_key` VARCHAR(191) NOT NULL COLLATE \'utf8mb4_general_ci\' AFTER `id`;
');
		$this->query('UPDATE biebboek SET categorie_id = 1000 WHERE categorie_id = 0');
		$this->query('UPDATE forum_draden SET laatste_post_id = null WHERE laatste_post_id NOT IN (SELECT post_id FROM forum_posts)');
		$this->query('DELETE FROM forum_posts WHERE draad_id NOT IN (SELECT draad_id FROM forum_draden)');
		$this->query('ALTER TABLE `lichting`
	COLLATE=\'utf8mb4_general_ci\',
	CONVERT TO CHARSET utf8mb4;
');
	}

	public function down() {
		$this->query('UPDATE profielen SET patroon = \'\' WHERE patroon IS NULL');
		$this->query('ALTER TABLE `forumplaatjes`
	COLLATE=\'utf8_general_ci\',
	CONVERT TO CHARSET utf8;
');
		$this->query('ALTER TABLE `lichting`
	COLLATE=\'utf8mb4_unicode_ci\',
	CONVERT TO CHARSET utf8mb4;
');
	}
}
