<?php

use Phinx\Migration\AbstractMigration;

class CourantMigratie extends AbstractMigration {
	public function change() {
		$this->table('courant')
			->removeColumn('template')
			->update();

		$this->table('courantbericht')
			->removeColumn('courantId')
			->update();
	}
}
