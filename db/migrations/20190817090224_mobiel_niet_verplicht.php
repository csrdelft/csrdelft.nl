<?php

use Phinx\Migration\AbstractMigration;

class MobielNietVerplicht extends AbstractMigration {
	public function down() {
		$this->query("UPDATE profielen SET mobiel = \"\" WHERE mobiel IS NULL");
		$this->query("ALTER TABLE profielen MODIFY mobiel VARCHAR(255) NOT NULL");
	}

	public function up() {
		$this->query("ALTER TABLE profielen MODIFY mobiel VARCHAR(255) NULL");
	}
}
