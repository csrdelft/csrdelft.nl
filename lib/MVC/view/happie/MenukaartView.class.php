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
		$this->dataUrl = happieUrl . '/overzicht';

		$this->addColumn('groep_beschikbaar', 'html-num-fmt');

		$nieuw = new DataTableKnop('>= 0', happieUrl . '/nieuw', null, 78, 'Nieuw', 'Nieuw menukaart-item', 'DTTT_button_new');
		$this->addKnop($nieuw);

		$wijzig = new DataTableKnop('== 1', happieUrl . '/wijzig/', null, 69, 'Wijzig', 'Wijzig menukaart-item', 'DTTT_button_edit');
		$wijzig->onclick = "location.href=this.href+fnGetSelectedObjectId(tableId);";
		$this->addKnop($wijzig);
	}

}

class HappieMenukaartItemsData extends DataTableResponse {

	public function getJson($item) {
		$array = $item->jsonSerialize();

		$groep = $item->getGroep();
		if ($groep) {
			$array['menukaart_groep'] = $groep->naam;
			$array['groep_beschikbaar'] = $groep->aantal_beschikbaar;
		}
		$array['prijs'] = $item->getPrijsFormatted();
		$array['beschrijving'] = nl2br($item->beschrijving);

		return parent::getJson($array);
	}

}

class HappieMenukaartGroepenView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartGroepenModel::orm, get_class($this), 'Menukaart groepen', 'gang');
		$this->dataUrl = happieUrl . '/overzicht';

		$nieuw = new DataTableKnop('>= 0', happieUrl . '/nieuw', null, 78, 'Nieuw', 'Nieuw menukaart-groep', 'DTTT_button_new');
		$this->addKnop($nieuw);

		$wijzig = new DataTableKnop('== 1', happieUrl . '/wijzig/', null, 69, 'Wijzig', 'Wijzig menukaart-groep', 'DTTT_button_edit');
		$wijzig->onclick = "location.href=this.href+fnGetSelectedObjectId(tableId);";
		$this->addKnop($wijzig);
	}

}

class HappieMenukaartGroepenData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['gang'] = HappieGang::format($groep->gang);

		return parent::getJson($array);
	}

}
