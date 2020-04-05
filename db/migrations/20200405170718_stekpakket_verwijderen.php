<?php

use Phinx\Migration\AbstractMigration;

class StekpakketVerwijderen extends AbstractMigration {
	public function change() {
		$this->table('stekpakket')
			->drop()
			->save();
	}
}
