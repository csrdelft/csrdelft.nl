<?php
use Phinx\Migration\AbstractMigration;
class OWeeCieCategorie extends AbstractMigration {
	public function change() {
		$this->table('CiviCategorie')
			->changeColumn('cie', 'enum', ['values' => ['anders', 'soccie', 'maalcie', 'oweecie']])
			->insert(['type' => 'Zonnebrillen', 'cie' => 'oweecie', 'status' => 1])
			->save();
		$this->table('CiviBestelling')
			->changeColumn('cie', 'enum', ['values' => ['anders', 'soccie', 'maalcie', 'oweecie']])
			->save();
	}
}
