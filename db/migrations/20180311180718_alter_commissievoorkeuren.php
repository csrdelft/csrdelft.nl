<?php


use Phinx\Migration\AbstractMigration;

class AlterCommissievoorkeuren extends AbstractMigration {
	public function change() {
		$this->table('voorkeurVoorkeur')->removeColumn('actief')->update();
		$this->table('voorkeurCommissie')->changeColumn('zichtbaar', 'boolean')->update();

	}
}
