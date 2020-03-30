<?php

use Phinx\Migration\AbstractMigration;

class VoegStekPakketToe extends AbstractMigration
{
    public function change()
    {
			$this->table('stekPakket', ['id' => false, 'primary_key' => ['uid']])
				->addColumn('uid', 'string', ['limit' => 4])
				->addColumn('basispakket', 'string')
				->addColumn('prijs', 'decimal')
				->addColumn('opties', 'text')
				->addColumn('timestamp', 'datetime')
				->addIndex('uid')
				->create();
    }
}
