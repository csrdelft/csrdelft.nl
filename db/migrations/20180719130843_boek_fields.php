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
			ALTER TABLE biebcategorie CHANGE id id int(11) NOT NULL auto_increment;
			ALTER TABLE biebcategorie CHANGE categorie categorie varchar(255) NOT NULL;
			ALTER TABLE biebbeschrijving CHANGE schrijver_uid schrijver_uid varchar(255) NOT NULL;
			ALTER TABLE biebexemplaar CHANGE eigenaar_uid eigenaar_uid varchar(255) NOT NULL;
			ALTER TABLE biebexemplaar CHANGE opmerking opmerking text NOT NULL;
			ALTER TABLE biebexemplaar CHANGE uitgeleend_uid uitgeleend_uid varchar(255) NULL DEFAULT NULL;
			ALTER TABLE biebexemplaar CHANGE uitleendatum uitleendatum datetime NULL DEFAULT NULL;
SQL
);
    }

    public function down() {
	}
}
