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

	public function __construct() {
		parent::__construct(HappieBestellingenModel::orm, get_class($this), 2, false, 'Overzicht bestellingen');

		$knop = new DataTableToolbarKnop('>= 0', null, 'rowcount', 'Count', 'Count selected rows', null);
		$knop->onclick = "alert($('#" . $this->tableId . " tbody tr.selected').length + ' row(s) selected');";

		$toolbar = new DataTableToolbar();
		$toolbar->addKnop($knop);

		$fields[] = $toolbar;
		$this->addFields($fields);
	}

}

class HappieBestellingWijzigenForm extends Formulier {

	public function __construct(Bestelling $bestelling) {
		parent::__construct($bestelling, get_class($this), happieUrl . '/wijzigen/' . $bestelling->bestelling_id, 'Bestelling wijzigen');
		$this->generateFields();
	}

}

class HappieBestelForm extends TabsForm {

	public function __construct($tafelNr, array $bestellingen = null) {
		parent::__construct($bestellingen, get_class($this), happieUrl . '/nieuw/' . $tafelNr, 'Bestellingen van tafel ' . $tafelNr);

		// maak tabs voor elke gang
		$this->setTabs(HappieGang::getTypeOptions());
		$groepen = HappieMenukaartItemsModel::instance()->getMenukaart();

		// maak invoerveld voor elk item
		foreach ($groepen as $groep) {

			// groepeer items
			$fields = array();
			$fields[] = new Subkopje($groep->titel);

			foreach ($groep->getItems() as $item) {

				// preload bestelling aantal
				if (isset($bestellingen[$item->item_id])) {
					$value = $bestellingen[$item->item_id]->aantal;
					$allergie = $bestellingen[$item->item_id]->klant_allergie;
				} else {
					$value = 0;
					$allergie = '';
				}

				$fields[] = new IntField('item' . $item->item_id, $value, $item->naam, 0, $item->aantal_beschikbaar);
				$fields[] = new HtmlComment(<<<HTML
<div onclick="$(this).slideUp();$('#expand_{$item->item_id}').slideDown();">beschrijving & klant allergie</div>
<div id="expand_{$item->item_id}" class="hidden">
HTML
				);
				$fields[] = new TextField('allergie' . $item->item_id, $allergie, 'Allergie');
				$fields[] = new HtmlComment('</div>');
			}

			// voeg groep toe aan tab
			$this->addFields($fields, $groep->gang);
		}
	}

}
