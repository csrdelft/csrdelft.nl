<?php

use Phinx\Migration\AbstractMigration;

class SecundairEmail extends AbstractMigration
{
	public function change()
	{
		$this->table('profielen')
			->addColumn('sec_email', 'string', ['null' => true])
			->save();
	}
}
