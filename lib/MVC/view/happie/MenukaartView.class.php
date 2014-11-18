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
		if ($data->getGroep()) {
			$data->groep_id = $data->getGroep()->titel;
		} else {
			$data->groep_id = 'Geen groep';
		}
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

		$nieuw = new DataTableToolbarKnop('>= 0', happieUrl . '/nieuw', '', 'Nieuw', 'Nieuw menukaart-item', '/famfamfam/add.png');
		$toolbar->addKnop($nieuw);

		$wijzig = new DataTableToolbarKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig menukaart-item', '/famfamfam/pencil.png');
		$toolbar->addKnop($wijzig);
	}

}

class HappieMenukaartItemForm extends Formulier {

	public function __construct(HappieMenukaartItem $item, $action = '/nieuw', $titel = 'Nieuw menukaart-item') {
		parent::__construct($item, get_class($this), happieUrl . $action, $titel);

		$groepen = HappieMenukaartGroepenModel::instance()->prefetch();
		$opties = array();
		foreach ($groepen as $groep) {
			$opties[$groep->groep_id] = $groep->titel;
		}
		$fields[] = new SelectField('groep_id', $item->groep_id, 'Menugroep', $opties);
		$fields[] = new RequiredTextField('naam', $item->naam, 'Gerechtnaam');
		$fields[] = new TextareaField('beschrijving', $item->beschrijving, 'Omschrijving');
		$fields[] = new TextareaField('allergie_info', $item->allergie_info, 'Allergie-informatie');
		$fields[] = new BedragField('prijs', $item->prijs, 'Prijs');
		$fields[] = new RequiredIntField('aantal_beschikbaar', $item->aantal_beschikbaar, 'Beschikbaar #');

		$fields[] = new FormDefaultKnoppen(happieUrl . '/serveer');
		$this->addFields($fields);
	}

}

class HappieMenukaartItemWijzigenForm extends HappieMenukaartItemForm {

	public function __construct(HappieMenukaartItem $item) {
		parent::__construct($item, '/wijzig/' . $item->item_id, 'Menukaart-item wijzigen');
	}

}

class HappieMenukaartGroepenJson extends DataTableResponse {

	public function getJson($data) {
		return parent::getJson($data);
	}

}

class HappieMenukaartGroepenView extends DataTable {

	public function __construct() {
		parent::__construct(HappieMenukaartGroepenModel::orm, get_class($this), 'Menukaart groepen', 'gang');
		$this->dataSource = happieUrl . '/data';

		$toolbar = new DataTableToolbar();
		$fields[] = $toolbar;
		$this->addFields($fields);

		$nieuw = new DataTableToolbarKnop('>= 0', happieUrl . '/nieuw', '', 'Nieuw', 'Nieuw menukaart-groep', '/famfamfam/add.png');
		$toolbar->addKnop($nieuw);

		$wijzig = new DataTableToolbarKnop('== 1', happieUrl . '/wijzig/', '', 'Wijzig', 'Wijzig menukaart-groep', '/famfamfam/pencil.png');
		$toolbar->addKnop($wijzig);
	}

}

class HappieMenukaartGroepForm extends Formulier {

	public function __construct(HappieMenukaartGroep $groep, $action = '/nieuw', $titel = 'Nieuwe menukaart-groep') {
		parent::__construct($groep, get_class($this), happieUrl . $action, $titel);
		$this->generateFields();

		$fields[] = new FormDefaultKnoppen(happieUrl . '/serveer');
		$this->addFields($fields);
	}

}

class HappieMenukaartGroepWijzigenForm extends HappieMenukaartGroepForm {

	public function __construct(HappieMenukaartGroep $groep) {
		parent::__construct($groep, '/wijzig/' . $groep->groep_id, 'Menukaart-groep wijzigen');
	}

}
