<?php

use Phinx\Migration\AbstractMigration;

class ProfielOptiesToevoegen extends AbstractMigration
{
    public function change()
    {
			$this->table('profielen')
				->addColumn('profielOpties', 'string', ['default' => ''])
				->save();
    }
}
