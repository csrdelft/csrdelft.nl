<?php

use Phinx\Migration\AbstractMigration;

class SetCiviSaldoDeletedDefaultFalse extends AbstractMigration
{
    public function change()
    {
			$this->table('CiviSaldo')
				->changeColumn('deleted', 'boolean', ['default'=> false])
				->update();
    }
}
