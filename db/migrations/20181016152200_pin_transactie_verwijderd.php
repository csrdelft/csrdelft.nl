<?php


use Phinx\Migration\AbstractMigration;

class PinTransactieVerwijderd extends AbstractMigration {

	public function change() {
		$this->table('pin_transactie_match')
			->changeColumn('status', 'enum', ['values' => ['match','verwijderd','verkeerd bedrag','missende transactie','missende bestelling']])
			->save();
    }
}
