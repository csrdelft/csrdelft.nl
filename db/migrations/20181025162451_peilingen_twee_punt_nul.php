<?php


use Phinx\Migration\AbstractMigration;

class PeilingenTweePuntNul extends AbstractMigration
{
	public function change()
	{
		$this->table('peiling')
			->renameColumn('tekst', 'beschrijving')
			->addColumn('eigenaar', 'string', ['length' => 4, 'collation' => 'utf8_general_ci'])
			->addColumn('mag_bewerken', 'boolean', ['default' => false])
			->addColumn('resultaat_zichtbaar', 'boolean', ['default' => true])
			->addColumn('aantal_voorstellen', 'integer', ['default' => 0])
			->addColumn('aantal_stemmen', 'integer', ['default' => 1])
			->addColumn('rechten_stemmen', 'string', ['null' => true])
		  ->save();

		$this->table('peiling_optie')
			->renameColumn('optie', 'titel')
			->addColumn('beschrijving', 'text', ['null' => true])
			->addColumn('ingebracht_door', 'string', ['length' => 4, 'collation' => 'utf8_general_ci', 'null' => true])
		  ->save();

		$this->table('peiling_stemmen')
			->addColumn('aantal', 'integer', ['default' => 1])
			->save();
	}
}
