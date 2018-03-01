<?php


use Phinx\Migration\AbstractMigration;

class CiviSaldoLogData extends AbstractMigration {
	/**
	 * Maak data kolom in CiviLog 'text'.
	 */
	public function change() {
		$this->table('CiviLog')
			->changeColumn('data', 'text')
			->save();
	}
}
