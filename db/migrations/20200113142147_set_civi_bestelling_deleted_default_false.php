<?php

use Phinx\Migration\AbstractMigration;

class SetCiviBestellingDeletedDefaultFalse extends AbstractMigration
{
    public function change()
    {
			$this->table('CiviBestelling')
				->changeColumn('deleted', 'boolean', ['default'=> false])
				->update();
    }
}
