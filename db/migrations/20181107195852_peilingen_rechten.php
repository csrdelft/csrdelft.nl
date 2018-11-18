<?php

use Phinx\Migration\AbstractMigration;

class PeilingenRechten extends AbstractMigration {
	public function change() {
		$this->table('peiling')
			->addColumn('rechten_mod', 'string', ['null' => true])
			->save();
	}
}
