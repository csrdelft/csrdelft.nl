<?php

/**
 * BestellingenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle bestellingen om te beheren.
 * 
 */
class HappieBestellingenView extends DataTable {

	protected $toolbar;

	public function __construct($titel = 'Alle bestellingen', $groupByColumn = 'datum') {
		parent::__construct(HappieBestellingenModel::orm, get_class($this), $titel, $groupByColumn, true);
		$this->dataSource = happieUrl . '/data/';
		$this->defaultLength = 100;

		$this->addColumn('menu_groep', 'html', 'menukaart_item');

		$this->hideColumn('datum');
		$this->hideColumn('wijzig_historie');
		$this->searchColumn('tafel');
		$this->searchColumn('financien_status');
		$this->searchColumn('menu_groep');
		$this->searchColumn('menukaart_item');

		$this->toolbar = new DataTableToolbar();
		$fields[] = $this->toolbar;
		$this->addFields($fields);

		$count = new DataTableToolbarKnop('>= 0', null, 'rowcount', 'Count', 'Count selected rows', null);
		$count->onclick = "alert(fnGetSelectionSize(tableId) + ' row(s) selected');";
		$this->toolbar->addKnop($count);
	}

}

class HappieKeukenView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Keuken actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');

		$this->hideColumn('financien_status');
		$this->searchColumn('financien_status', false);
		$this->searchColumn('serveer_status');
	}

}

class HappieServeerView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Serveer actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');

		$this->hideColumn('financien_status');
		$this->searchColumn('financien_status', false);
		$this->searchColumn('serveer_status');
	}

}

class HappieBarView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Bar actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');

		$this->hideColumn('serveer_status');
	}

}

class HappieKassaView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Kassa actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');
	}

}
