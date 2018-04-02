<?php


use Phinx\Migration\AbstractMigration;

class MakeOpmerkingNullable extends AbstractMigration
{
    public function change()
    {
		$this->query("ALTER TABLE voorkeurOpmerking CHANGE lidOpmerking lidOpmerking text NULL DEFAULT NULL;");
		$this->query("ALTER TABLE voorkeurOpmerking CHANGE praesesOpmerking praesesOpmerking text NULL DEFAULT NULL;");
    }
}
