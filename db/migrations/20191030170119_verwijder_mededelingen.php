<?php

use Phinx\Migration\AbstractMigration;

class VerwijderMededelingen extends AbstractMigration
{
    public function change()
		{
			$this->table('mededelingen')->drop()->save();
			$this->table('mededelingcategorie')->drop()->save();
		}

}
