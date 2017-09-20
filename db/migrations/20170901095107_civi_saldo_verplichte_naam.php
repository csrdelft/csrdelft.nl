<?php

use Phinx\Migration\AbstractMigration;

class CiviSaldoVerplichteNaam extends AbstractMigration
{
    /**
     * Maak kolom naam in CiviSaldo verplicht. #325
     */
    public function change()
    {
		$this->table('CiviSaldo')
			->changeColumn('naam', 'string', ['null' => false])
			->save();
    }
}
