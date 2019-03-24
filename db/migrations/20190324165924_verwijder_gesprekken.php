<?php


use Phinx\Migration\AbstractMigration;

class VerwijderGesprekken extends AbstractMigration
{
    public function up()
    {
    	$this->table('gesprek_berichten')->drop()->save();
    	$this->table('gesprek_deelnemers')->drop()->save();
			$this->table('gesprekken')->drop()->save();
    }

    public function down()
		{
			// Niets
		}
}
