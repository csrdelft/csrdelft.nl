<?php

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
		$this->dataSource = happieUrl . '/overzicht';

		$nieuw = new DataTableKnop('>= 0', happieUrl . '/nieuw', '', 'Nieuw', 'Nieuw menukaart-item', '/famfamfam/add.png');
		$this->addKnop($nieuw);

		$wijzig = new DataTableKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig menukaart-item', '/famfamfam/pencil.png');
		$wijzig->onclick = "this.href+=fnGetSelectedObjectId(tableId);";
		$this->addKnop($wijzig);
	}

}

class HappieMenukaartItemsData extends DataTableResponse {

	public function getJson($item) {
		$array = $item->jsonSerialize();

		$groep = $item->getGroep();
		if ($groep) {
			$array['menukaart_groep'] = $groep->naam;
			$array['aantal_beschikbaar'] .= ' / ' . $groep->aantal_beschikbaar;
		}
		$array['prijs'] = $item->getPrijsFormatted();
		$array['beschrijving'] = nl2br($item->beschrijving);

		return parent::getJson($array);
	}

}

class HappieMenukaartGroepenView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartGroepenModel::orm, get_class($this), 'Menukaart groepen', 'gang');
		$this->dataSource = happieUrl . '/overzicht';

		$nieuw = new DataTableKnop('>= 0', happieUrl . '/nieuw', '', 'Nieuw', 'Nieuw menukaart-groep', '/famfamfam/add.png');
		$this->addKnop($nieuw);

		$wijzig = new DataTableKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig menukaart-groep', '/famfamfam/pencil.png');
		$wijzig->onclick = "this.href+=fnGetSelectedObjectId(tableId);";
		$this->addKnop($wijzig);
	}

}

class HappieMenukaartGroepenData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['gang'] = $groep->getGangFormatted();

		return parent::getJson($array);
	}

}
