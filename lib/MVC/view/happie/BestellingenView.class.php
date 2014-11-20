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

	public function __construct($dataSource = '/overzicht', $titel = 'Alle bestellingen', $groupByColumn = 'datum') {
		parent::__construct(HappieBestellingenModel::orm, get_class($this), $titel, $groupByColumn);
		$this->dataSource = happieUrl . $dataSource;

		$this->addColumn('menu_groep', 'html', 'menukaart_item');

		$this->hideColumn('datum');
		$this->hideColumn('wijzig_historie');
		$this->searchColumn('tafel');
		$this->searchColumn('financien_status');
		$this->searchColumn('menu_groep');
		$this->searchColumn('menukaart_item');

		$wijzig = new DataTableToolbarKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig bestelling', '/famfamfam/pencil.png');
		$wijzig->onclick = "this.href+=fnGetSelectedObjectId(tableId);";
		$this->toolbar->addKnop($wijzig);
	}

}

class HappieServeerView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('/serveer', 'Serveer actueel', 'tafel');

		$this->hideColumn('financien_status');
		$this->searchColumn('financien_status', false);
		$this->searchColumn('serveer_status');
	}

}

class HappieKeukenView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('/keuken', 'Keuken actueel', 'tafel');

		$this->hideColumn('financien_status');
		$this->searchColumn('financien_status', false);
		$this->searchColumn('serveer_status');
	}

}

class HappieBarView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('/bar', 'Bar actueel', 'tafel');
	}

}

class HappieKassaView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('/kassa', 'Kassa actueel', 'tafel');
	}

}
