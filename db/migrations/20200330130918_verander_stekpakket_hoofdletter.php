<?php

use Phinx\Migration\AbstractMigration;

class VeranderStekpakketHoofdletter extends AbstractMigration {
	public function up() {
		$this->table('stekPakket')
			->rename('stekpakket')
			->save();
	}

	public function down() {
		$this->table('stekpakket')
			->rename('stekPakket')
			->save();
	}
}
