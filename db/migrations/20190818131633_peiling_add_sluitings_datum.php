<?php

use Phinx\Migration\AbstractMigration;

class PeilingAddSluitingsDatum extends AbstractMigration
{

    public function change()
    {
			$this->table('peiling')
				->addColumn('sluitingsdatum', 'datetime', ['null' => true])
				->save();
    }
}
