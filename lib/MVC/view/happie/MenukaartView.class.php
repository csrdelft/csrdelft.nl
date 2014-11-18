<?php

/**
 * MenukaartView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle groepen en items op de menukaart om te beheren.
 * 
 */
class HappieMenukaartItemsJson extends DataTableResponse {

	public function getJson($data) {
		$data->groep_id = $data->getGroep()->titel;
		return parent::getJson($data);
	}

}

class HappieMenukaartItemsView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartItemsModel::orm, get_class($this), 'Menukaart', 'groep_id');
		$this->dataSource = happieUrl . '/data';

		$toolbar = new DataTableToolbar();
		$fields[] = $toolbar;
		$this->addFields($fields);

		$count = new DataTableToolbarKnop('>= 0', null, 'rowcount', 'Count', 'Count selected rows', null);
		$count->onclick = "alert($('#" . $this->tableId . " tbody tr.selected').length + ' row(s) selected');";
		$toolbar->addKnop($count);

		$print = new DataTableToolbarKnop('== 1', null, 'debugprint', 'Print', 'Debugprint row', null);
		$print->onclick = "console.log($('#" . $this->tableId . " tbody tr.selected'));";
		$toolbar->addKnop($print);
	}

}

class HappieMenukaartItemForm extends Formulier {

	public function __construct(appieMenukaartItem $item = null, $action = '/nieuw', $titel = 'Nieuw menukaart-item') {
		parent::__construct($item, get_class($this), happieUrl . $action, $titel);
	}

}

class HappieMenukaartItemWijzigenForm extends HappieMenukaartItemForm {

	public function __construct(HappieMenukaartItem $item) {
		parent::__construct($item, '/wijzig/' . $item->item_id, 'Menukaart-item wijzigen');
		$this->generateFields();
	}

}

class HappieMenukaartGroepenJson extends DataTableResponse {

	public function getJson($data) {
		return parent::getJson($data);
	}

}

class HappieMenukaartGroepenView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartItemsModel::orm, get_class($this), 'Menukaart', 'gang');
		$this->dataSource = happieUrl . '/data';

		$toolbar = new DataTableToolbar();
		$fields[] = $toolbar;
		$this->addFields($fields);

		$count = new DataTableToolbarKnop('>= 0', null, 'rowcount', 'Count', 'Count selected rows', null);
		$count->onclick = "alert($('#" . $this->tableId . " tbody tr.selected').length + ' row(s) selected');";
		$toolbar->addKnop($count);

		$print = new DataTableToolbarKnop('== 1', null, 'debugprint', 'Print', 'Debugprint row', null);
		$print->onclick = "console.log($('#" . $this->tableId . " tbody tr.selected'));";
		$toolbar->addKnop($print);
	}

}

class HappieMenukaartGroepForm extends Formulier {

	public function __construct(HappieMenukaartGroep $groep = null, $action = '/nieuw', $titel = 'Nieuwe menukaart-groep') {
		parent::__construct($groep, get_class($this), happieUrl . $action, $titel);
		$this->generateFields();
	}

}

class HappieMenukaartGroepWijzigenForm extends HappieMenukaartGroepForm {

	public function __construct(HappieMenukaartGroep $groep) {
		parent::__construct($groep, '/wijzig/' . $groep->groep_id, 'Menukaart-groep wijzigen');
		$this->generateFields();
	}

}
