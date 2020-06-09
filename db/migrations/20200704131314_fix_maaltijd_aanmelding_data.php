<?php

use Phinx\Migration\AbstractMigration;

class FixMaaltijdAanmeldingData extends AbstractMigration {
	public function up() {
		$this->query('UPDATE mlt_aanmeldingen SET uid = door_uid WHERE uid = \'\'');
	}

	public function down() {
		// waarom wil je de data weer vies maken?
	}
}
