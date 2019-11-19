<?php

use Phinx\Migration\AbstractMigration;

class VerwijderGeolocation extends AbstractMigration {
	public function up() {
		$this->table('geolocation')->drop()->save();
	}

	public function down() {
		// sorry
	}
}
