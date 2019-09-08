<?php

use Phinx\Migration\AbstractMigration;

class CourantRewrite extends AbstractMigration
{
    public function change()
    {
    	$this->table('courantbericht')
				->changeColumn('datumTijd', 'datetime', ['default' => null])
				->changeColumn('courantId', 'integer', ['null' => true])
				->changeColumn('titel', 'string')
				->renameColumn('ID', 'id')
				->renameColumn('courantID', 'courantId')
				->update();

    	$this->table('courant')
				->changeColumn('template', 'string')
				->changeColumn('verzendMoment', 'datetime', ['default' => null])
				->renameColumn('ID', 'id')
				->update();
    }
}
