<?php


use Phinx\Migration\AbstractMigration;

class BoekFields extends AbstractMigration
{
    public function up()
    {
		$this->query(<<<SQL
 			ALTER TABLE biebboek CHANGE auteur auteur varchar(255) NOT NULL;
			ALTER TABLE biebboek CHANGE titel titel varchar(255) NOT NULL;
			ALTER TABLE biebboek CHANGE taal taal varchar(255) NOT NULL;
			ALTER TABLE biebboek CHANGE isbn isbn varchar(255) NOT NULL;
			ALTER TABLE biebboek CHANGE paginas paginas int(11) NOT NULL;
			ALTER TABLE biebboek CHANGE uitgavejaar uitgavejaar int(11) NOT NULL;
			 ALTER TABLE biebboek CHANGE uitgeverij uitgeverij varchar(255) NOT NULL;
			 ALTER TABLE biebboek CHANGE code code varchar(255) NOT NULL; 
SQL
);
    }

    public function down() {
	}
}
