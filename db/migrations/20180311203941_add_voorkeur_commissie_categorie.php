<?php


use Phinx\Migration\AbstractMigration;

class AddVoorkeurCommissieCategorie extends AbstractMigration {
	public function change() {
		$this->table('voorkeurCommissieCategorie')
			->addColumn('naam', 'string')
			->create();
		$this->table('voorkeurCommissieCategorie')->insert(array('naam' => 'C.S.R.'))
			->save();
		$id = 1;
		$this->table('voorkeurCommissie')->addColumn('categorie_id', 'integer', array('default' => $id, 'null' => false))
			->addForeignKey('categorie_id', 'voorkeurCommissieCategorie')
			->update();
	}
}
