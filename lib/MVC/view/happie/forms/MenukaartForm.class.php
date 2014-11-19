<?php

/**
 * MenukaartForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Menukaart invoer / wijzigen formulieren.
 * 
 */
class HappieMenukaartItemForm extends Formulier {

	public function __construct(HappieMenukaartItem $item, $action = '/nieuw', $titel = 'Nieuw menukaart-item') {
		parent::__construct($item, get_class($this), happieUrl . $action, $titel);

		$groepen = HappieMenukaartGroepenModel::instance()->prefetch();
		$opties = array();
		foreach ($groepen as $groep) {
			$opties[$groep->groep_id] = $groep->naam;
		}
		$fields[] = new SelectField('menukaart_groep', $item->menukaart_groep, 'Menugroep', $opties);
		$fields[] = new TextField('naam', $item->naam, 'Gerechtnaam', 100, 3);
		$fields[] = new TextareaField('beschrijving', $item->beschrijving, 'Omschrijving');
		$fields[] = new TextField('allergie_info', $item->allergie_info, 'Allergie-informatie');
		$fields[] = new BedragField('prijs', $item->prijs, 'Prijs', '€', 0);
		$fields[] = new RequiredIntField('aantal_beschikbaar', $item->aantal_beschikbaar, 'Beschikbaar #', 0);

		$fields[] = new FormDefaultKnoppen(happieUrl . '/overzicht');
		$this->addFields($fields);
	}

}

class HappieMenukaartItemWijzigenForm extends HappieMenukaartItemForm {

	public function __construct(HappieMenukaartItem $item) {
		parent::__construct($item, '/wijzig/' . $item->item_id, 'Menukaart-item wijzigen');
	}

}

class HappieMenukaartGroepForm extends Formulier {

	public function __construct(HappieMenukaartGroep $groep, $action = '/nieuw', $titel = 'Nieuwe menukaart-groep') {
		parent::__construct($groep, get_class($this), happieUrl . $action, $titel);

		$opties = array();
		foreach (HappieGang::getTypeOptions() as $gang) {
			$opties[$gang] = $gang;
		}
		$fields[] = new SelectField('gang', $groep->gang, 'Gang', $opties);
		$fields[] = new TextField('naam', $groep->naam, 'Groepnaam', 100, 3);
		$fields[] = new RequiredIntField('aantal_beschikbaar', $groep->aantal_beschikbaar, 'Beschikbaar #', 0);

		$fields[] = new FormDefaultKnoppen(happieUrl . '/overzicht');
		$this->addFields($fields);
	}

}

class HappieMenukaartGroepWijzigenForm extends HappieMenukaartGroepForm {

	public function __construct(HappieMenukaartGroep $groep) {
		parent::__construct($groep, '/wijzig/' . $groep->groep_id, 'Menukaart-groep wijzigen');
	}

}
