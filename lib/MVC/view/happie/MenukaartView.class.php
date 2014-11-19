<?php

require_once 'MVC/view/happie/forms/MenukaartForm.class.php';

/**
 * MenukaartView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle groepen en items op de menukaart om te beheren.
 * 
 */
class HappieMenukaartItemsView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartItemsModel::orm, get_class($this), 'Menukaart items', 'menukaart_groep');
		$this->dataSource = happieUrl . '/data';
		$this->defaultLength = 100;

		$this->invisible[] = 'prijs';
		$this->invisible[] = 'beschrijving';
		$this->invisible[] = 'allergie_info';

		$this->nosearch[] = 'aantal_beschikbaar';

		$fields['t'] = new DataTableToolbar();

		$nieuw = new DataTableToolbarKnop('>= 0', happieUrl . '/nieuw', '', 'Nieuw', 'Nieuw menukaart-item', '/famfamfam/add.png');
		$fields['t']->addKnop($nieuw);

		$wijzig = new DataTableToolbarKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig menukaart-item', '/famfamfam/pencil.png');
		$wijzig->onclick = "this.href+=fnGetSelectedObjectId(tableId);";
		$fields['t']->addKnop($wijzig);

		$this->addFields($fields);
	}

}

class HappieMenukaartGroepenView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartGroepenModel::orm, get_class($this), 'Menukaart groepen', 'gang');
		$this->dataSource = happieUrl . '/data';
		$this->defaultLength = 100;

		$this->nosearch[] = 'aantal_beschikbaar';

		$fields['t'] = new DataTableToolbar();

		$nieuw = new DataTableToolbarKnop('>= 0', happieUrl . '/nieuw', '', 'Nieuw', 'Nieuw menukaart-groep', '/famfamfam/add.png');
		$fields['t']->addKnop($nieuw);

		$wijzig = new DataTableToolbarKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig menukaart-groep', '/famfamfam/pencil.png');
		$wijzig->onclick = "this.href+=fnGetSelectedObjectId(tableId);";
		$fields['t']->addKnop($wijzig);

		$this->addFields($fields);
	}

}
