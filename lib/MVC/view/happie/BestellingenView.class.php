<?php

require_once 'MVC/model/entity/happie/HappieGang.enum.php';

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

		$this->addColumn('menu_groep', 'menu_groep', 'Menukaart groep', 'html');

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
	}

}

class HappieServeerView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Serveer actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');
	}

}

class HappieBarView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Bar actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');
	}

}

class HappieKassaView extends HappieBestellingenView {

	public function __construct() {
		parent::__construct('Kassa actueel', 'tafel');
		$this->dataSource .= date('Y/m/d');
	}

}

class HappieBestelForm extends TabsForm {

	private $js = '';

	public function __construct() {
		parent::__construct(null, get_class($this), happieUrl . '/nieuw', 'Nieuwe bestelling');
		$this->setTabs(HappieGang::getTypeOptions());

		// tafel invoer
		$fields[] = new SelectField('tafel', null, 'Tafel', range(0, 99)); // array index starts from 0
		$this->addFields($fields, 'head');

		$groepen = HappieMenukaartItemsModel::instance()->getMenukaart();

		// maak invoerveld voor elk item
		foreach ($groepen as $groep) {

			$fields = array();

			// groepeer items
			$fields[] = new Subkopje($groep->naam);

			foreach ($groep->getItems() as $item) {

				// preload bestelling aantal
				if (isset($bestellingen[$item->item_id])) {
					$aantal = $bestellingen[$item->item_id]->aantal;
					$opmerking = $bestellingen[$item->item_id]->opmerking;
				} else {
					$aantal = 0;
					$opmerking = '';
				}
				$beschikbaar = min($item->aantal_beschikbaar, $groep->aantal_beschikbaar);

				$toggle = <<<JS
$('#expand_{$item->item_id}').toggle().find('textarea:first').focus();
JS;
				$int = new IntField('item' . $item->item_id, $aantal, $item->naam, 0, $beschikbaar);
				$int->min_alert = 'Fout: minder dan 0';
				$int->max_alert = 'Fout: te weinig beschikbaar';
				$fields[] = $int;

				if ($beschikbaar > 0) {
					$comment = '<div id="toggle_' . $item->item_id . '" class="btn" style="margin-left:5px;padding:0 0.5em;" onclick="' . $toggle . '">';
					if (empty($item->allergie_info)) {
						$comment .= 'Info';
					} else {
						$comment .= $item->allergie_info;
					}
					$comment .= '</div>';
				} else {
					$comment = '<div id="toggle_' . $item->item_id . '" class="inline alert alert-warning" style="margin-left:5px;padding:0 .5em;">OP</div>';
				}
				$fields[] = new HtmlComment($comment . '<div id="expand_' . $item->item_id . '" style="display:none;"><div style="font-style:italic;">' . $item->beschrijving . '</div>');

				$opm = new TextareaField('opmerking' . $item->item_id, $opmerking);
				$opm->placeholder = 'Allergie van klant / opmerking';
				$fields[] = $opm;

				$fields[] = new HtmlComment('</div>');

				$this->js .= <<<JS
$('#toggle_{$item->item_id}').appendTo('#wrapper_{$int->getId()}');
$('#{$opm->getId()}').height('30px');
JS;
			}

			// voeg groep toe aan tab en maak tab voor elke gang
			$this->addFields($fields, $groep->gang);
		}

		$fields = array();

		$fields['btn'] = new FormDefaultKnoppen(happieUrl . '/serveer');
		$fields['btn']->confirmAll();

		$this->addFields($fields, 'foot');
	}

	public function getJavascript() {
		return parent::getJavascript() . $this->js;
	}

	/**
	 * Groepeer de waarden per item.
	 */
	public function getValues() {
		$tafel = (int) $this->findByName('tafel')->getValue();
		$values = array();
		foreach ($this->getFields() as $field) {
			// aantal veld
			if ($field instanceof IntField) {
				$item_id = (int) substr($field->getName(), 4);
				$values[$item_id]['aantal'] = $field->getValue();
				$values[$item_id]['tafel'] = $tafel;
			}
			// opmerking veld
			elseif ($field instanceof TextareaField) {
				$item_id = (int) substr($field->getName(), 9);
				$field->empty_null = true;
				$values[$item_id]['opmerking'] = $field->getValue();
			}
		}
		return $values;
	}

}

class HappieBestellingWijzigenForm extends Formulier {

	public function __construct(Bestelling $bestelling) {
		parent::__construct($bestelling, get_class($this), happieUrl . '/wijzigen/' . $bestelling->bestelling_id, 'Bestelling wijzigen');
		$this->generateFields();
	}

}
