<?php

use Phinx\Migration\AbstractMigration;

class EetplanOpmerking extends AbstractMigration {
	public function change() {
		$this->table('eetplan_bekenden')
			->addColumn('opmerking', 'string', ['null' => true])
			->update();

		$this->table('eetplan')
			->addColumn('opmerking', 'string', ['null' => true])
			->update();
	}
}
