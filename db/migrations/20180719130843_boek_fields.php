<?php


use Phinx\Migration\AbstractMigration;

class BoekFields extends AbstractMigration
{
    public function change()
    {
		$this->query(<<<SQL
 			ALTER TABLE biebboek CHANGE auteur auteur varchar(255) NOT NULL;
			ALTER TABLE biebboek ADD auteur_imp varchar(255) NOT NULL AFTER auteur;
			ALTER TABLE biebboek CHANGE titel titel varchar(255) NOT NULL;
			ALTER TABLE biebboek ADD titel_imp varchar(255) NOT NULL AFTER titel;
			ALTER TABLE biebboek CHANGE taal taal varchar(255) NOT NULL;
			ALTER TABLE biebboek ADD taal_imp varchar(255) NOT NULL AFTER taal;
			ALTER TABLE biebboek CHANGE isbn isbn varchar(255) NOT NULL;
			ALTER TABLE biebboek CHANGE paginas paginas int(11) NOT NULL;
			ALTER TABLE biebboek ADD paginas_imp int(11) NOT NULL AFTER paginas;
			ALTER TABLE biebboek CHANGE uitgavejaar uitgavejaar int(11) NOT NULL;
			 ALTER TABLE biebboek ADD uitgavejaar_imp int(11) NOT NULL AFTER uitgavejaar;
			 ALTER TABLE biebboek CHANGE uitgeverij uitgeverij varchar(255) NOT NULL;
			 ALTER TABLE biebboek ADD uitgeverij_imp varchar(255) NOT NULL AFTER uitgeverij;
			 ALTER TABLE biebboek CHANGE code code varchar(255) NOT NULL; 
SQL
);
    }
}
