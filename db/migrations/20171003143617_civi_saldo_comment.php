<?php

use Phinx\Migration\AbstractMigration;

class CiviSaldoComment extends AbstractMigration
{
    /**
     * Voeg column comment toe aan CiviBestelling
     */
    public function change()
    {
		$this->table('CiviBestelling')
			->addColumn('comment', 'string', ['null' => true])
			->update();
    }
}
