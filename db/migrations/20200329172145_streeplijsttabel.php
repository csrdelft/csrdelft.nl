<?php

use Phinx\Migration\AbstractMigration;

class Streeplijsttabel extends AbstractMigration
{
	public function change()
	{
		$this->table('streeplijsten')
			->addColumn('maker', 'string', ['length' => 4])
			->addColumn('aanmaakdatum', 'datetime')
			->addColumn('inhoud_streeplijst', 'text')
			->addColumn('naam_streeplijst', 'string', ['null' => true])
			->create();
	}
}
