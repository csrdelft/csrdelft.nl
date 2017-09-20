<?php
use Phinx\Migration\AbstractMigration;

class SocCieCiviSaldoFix extends AbstractMigration {
	public function change() {
		$this->table('CiviSaldo')
			->changeColumn('naam', 'text', ['null' => false, 'default' => ''])
			->changeColumn('uid', 'string', ['length' => 4, 'collation' => 'utf8_general_ci'])
			->addIndex('uid', ['unique' => true])
			->save();
		$this->table('CiviBestelling')
			->changeColumn('uid', 'string', ['length' => 4, 'collation' => 'utf8_general_ci'])
			->addForeignKey('uid', 'CiviSaldo', 'uid')
			->save();
	}
}
