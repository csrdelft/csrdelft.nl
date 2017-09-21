<?php
use Phinx\Migration\AbstractMigration;

class PinTransactieControle extends AbstractMigration {
	/**
	 * Maak de pin_transacties tabel aan voor het PinTransactie model.
	 */
	public function change() {
		$pinTransacties = $this->table('pin_transacties');
		$pinTransacties
			->addColumn('bestelling_id', 'integer', ['null' => true])
			->addColumn('datetime', 'string')
			->addColumn('brand', 'string')
			->addColumn('merchant', 'string')
			->addColumn('store', 'string')
			->addColumn('terminal', 'string')
			->addColumn('TID', 'string')
			->addColumn('MID', 'string')
			->addColumn('ref', 'string')
			->addColumn('type', 'string')
			->addColumn('amount', 'string')
			->addColumn('AUTRSP', 'string')
			->addColumn('STAN', 'string')
			->create();
	}
}
