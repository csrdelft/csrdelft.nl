<?php


use Phinx\Migration\AbstractMigration;

class PeilingenTweePuntNul extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 *
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 *
	 *    createTable
	 *    renameTable
	 *    addColumn
	 *    renameColumn
	 *    addIndex
	 *    addForeignKey
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		$this->table('peiling')
			->renameColumn('tekst', 'beschrijving')
			->addColumn('eigenaar', 'string', ['length' => 4, 'collation' => 'utf8_general_ci'])
			->addColumn('mag_bewerken', 'boolean', ['default' => false])
			->addColumn('resultaat_zichtbaar', 'boolean', ['default' => true])
			->addColumn('aantal_voorstellen', 'integer', ['default' => 0])
			->addColumn('aantal_stemmen', 'integer', ['default' => 1])
		  ->save();

		$this->table('peiling_optie')
			->renameColumn('optie', 'titel')
			->addColumn('beschrijving', 'text', ['null' => true])
			->addColumn('ingebracht_door', 'string', ['length' => 4, 'collation' => 'utf8_general_ci', 'null' => true])
		  ->save();
	}
}
