<?php

use Phinx\Migration\AbstractMigration;

class VeranderStekpakketNullable extends AbstractMigration {
	public function up() {
		$this->table('stekpakket')
			->changeColumn('opties', 'text', ['null' => true])
			->save();
	}

	public function down() {

	}
}
