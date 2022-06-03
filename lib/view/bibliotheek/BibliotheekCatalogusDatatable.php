<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;

class BibliotheekCatalogusDatatable extends DataTable {

	public function __construct() {
		parent::__construct(Boek::class, '/bibliotheek/catalogusdata', 'Bibliotheekcatalogus');
		$this->addKnop(new SourceChangeDataTableKnop('/bibliotheek/catalogusdata', 'Alle boeken', 'Toon alle boeken'));
		$this->addKnop(new SourceChangeDataTableKnop('/bibliotheek/catalogusdata?eigenaar=x222', 'C.S.R.-bibliotheek', 'Toon C.S.R.-bibliotheek'));
		$this->addKnop(new SourceChangeDataTableKnop('/bibliotheek/catalogusdata?eigenaar='. urlencode(LoginService::getUid()), 'Eigen boeken', 'Eigen boeken'));
		$this->settings['oLanguage'] = [
			'sZeroRecords' => 'Geen boeken gevonden',
			'sInfoEmtpy' => 'Geen boeken gevonden',
			'sSearch' => 'Zoeken:',
			'oPaginate' => [
				'sFirst' => 'Eerste',
				'sPrevious' => 'Vorige',
				'sNext' => 'Volgende',
				'sLast' => 'Laatste']
		];
		$this->defaultLength = 30;
		$this->settings['select'] = false;
		$this->settings['buttons'] = [];

		$this->hideColumn('auteur_id');
		$this->hideColumn('isbn');
		$this->hideColumn('categorie_id');
		$this->hideColumn('code');
		$this->hideColumn('titel');
		$this->addColumn('titel_link', 'auteur', null,null, 'titel');
		$this->setColumnTitle('titel_link', 'Titel');
		$this->setOrder(['auteur'=>'asc']);
		$this->searchColumn('titel');
		$this->searchColumn('auteur');
		$this->addColumn("#RC", null, null, null, null, null, "recensie_count");
	}


}
