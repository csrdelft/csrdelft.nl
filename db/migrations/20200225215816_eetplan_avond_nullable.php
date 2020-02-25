<?php

use Phinx\Migration\AbstractMigration;

class EetplanAvondNullable extends AbstractMigration
{
	public function up() {
		$this->table('eetplan')->changeColumn('avond', 'date', ['null' => true])->update();
		$this->query('UPDATE eetplan SET avond = NULL WHERE avond = "0000-00-00"');
	}

	public function down() {
		$this->query('UPDATE eetplan SET avond = "0000-00-00" WHERE avond = NULL');
		$this->table('eetplan')->changeColumn('avond', 'date', ['null' => false])->update();
	}
}
