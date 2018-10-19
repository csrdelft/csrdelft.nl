<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableResponse;

class BibliotheekCatalogusContent extends DataTable {

	public function __construct() {
		parent::__construct(BoekModel::ORM, '/bibliotheek/catalogusdata', 'Bibliotheekcatalogus');
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
		$this->setOrder(['titel'=>'asc', 'auteur'=> 'asc']);
		$this->hideColumn('auteur_id');
		$this->hideColumn('isbn');
		$this->hideColumn('categorie_id');
		$this->hideColumn('code');
		$this->searchColumn('titel');
		$this->searchColumn('auteur');
	}


}
