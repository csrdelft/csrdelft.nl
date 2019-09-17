<?php

use CsrDelft\model\entity\BtwTarieven;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class DigitaleDeclas extends AbstractMigration
{
    public function change()
    {
    	$this->table('declaratie')
				->addColumn('commissie', 'string')
				->addColumn('naam', 'string')
				->addColumn('email', 'string')
				->addColumn('datum', 'date')
				->addColumn('datum_invullen', 'date')
				->addColumn('iban', 'string')
				->addColumn('opmerkingen', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM])
				->create();

    	$this->table('declaratie_regel')
				->addColumn('declaratie_id', 'integer')
				->addColumn('datum', 'date')
				->addColumn('omschrijving', 'string')
				->addColumn('bedrag', 'string')
				->addColumn('btw_tarief', 'enum', ['values' => ['BTW_GEEN_0', 'BTW_VERLAAGD_9', 'BTW_ALGEMEEN_21']])
				->create();
    }
}
