<?php

use Phinx\Migration\AbstractMigration;

class PeilingEigenaarNullable extends AbstractMigration
{
	public function up() {
		$this->table('peiling')->changeColumn('eigenaar', 'string', ['length' => 4, 'null' => true])->update();
		$this->query('UPDATE peiling SET eigenaar = NULL WHERE eigenaar = ""');
	}

	public function down() {
		$this->query('UPDATE peiling SET eigenaar = "" WHERE eigenaar = NULL');
		$this->table('peiling')->changeColumn('eigenaar', 'string', ['length' => 4, 'null' => false])->update();
	}
}
