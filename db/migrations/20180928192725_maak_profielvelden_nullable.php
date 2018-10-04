<?php


use Phinx\Migration\AbstractMigration;

class MaakProfielveldenNullable extends AbstractMigration {

	public function change() {
		$this->table('profielen')
			->changeColumn('middelbareSchool', 'string', ['null' => true])
			->changeColumn('startkamp', 'string', ['null' => true])
			->changeColumn('matrixPlek', 'string', ['null' => true])
			->changeColumn('novietSoort', 'string', ['null' => true])
			->changeColumn('novitiaat', 'text', ['null' => true]);
    }
}
