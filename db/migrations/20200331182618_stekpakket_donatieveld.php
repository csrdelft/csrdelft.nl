<?php

use Phinx\Migration\AbstractMigration;

class StekpakketDonatieveld extends AbstractMigration {
	public function change() {
		$this->table('stekpakket')
			->addColumn('donatie', 'boolean', ['default' => false])
			->save();
	}
}
