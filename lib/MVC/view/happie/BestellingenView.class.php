<?php

/**
 * BestellingenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle bestellingen om te beheren.
 * 
 */
class HappieBestellingenData extends DataTableResponse {

	public function __construct($data) {
		parent::__construct('HappieBestellingen', $data);
	}

	public function getJson($bestelling) {
		$array = $bestelling->jsonSerialize();

		$beschikbaar = 0;
		$item = $bestelling->getItem($bestelling->menukaart_item);
		if ($item) {
			$array['menukaart_item'] = $item->naam;
			$groep = $item->getGroep();
			if ($groep) {
				$array['gang'] = HappieGang::format($groep->gang);
				$array['menu_groep'] = $groep->naam;
				$beschikbaar = min($item->aantal_beschikbaar, $groep->aantal_beschikbaar);
			} else {
				$array['menu_groep'] = 'Geen groep';
			}
		}
		$array['tafel'] = 'Tafel ' . $bestelling->tafel;
		$array['laatst_gewijzigd'] = reldate($bestelling->laatst_gewijzigd);
		$array['wijzig_historie'] = null;

		// editable aantal
		$field = new IntField('aantal', $bestelling->aantal, 'Aantal', 0, $bestelling->aantal + $beschikbaar);
		$form = new InlineForm($bestelling, 'aantal' . $bestelling->bestelling_id, happieUrl . '/aantal', $field);
		$form->css_classes[] = 'DataTableResponse';
		$array['aantal'] = $form->getHtml();

		// editable aantal geserveerd
		$field = new IntField('aantal_geserveerd', $bestelling->aantal_geserveerd, 'Aantal geserveerd', 0, $bestelling->aantal_geserveerd + $beschikbaar); // meer vrijheid dan $bestelling->aantal
		$form = new InlineForm($bestelling, 'geserveerd' . $bestelling->bestelling_id, happieUrl . '/geserveerd', $field);
		$form->css_classes[] = 'DataTableResponse';
		$array['aantal_geserveerd'] = $form->getHtml();

		// editable serveer status
		$field = new SelectField('serveer_status', $bestelling->serveer_status, 'Serveer status', HappieServeerStatus::getSelectOptions());
		$form = new InlineForm($bestelling, 'serveerstatus' . $bestelling->bestelling_id, happieUrl . '/serveerstatus', $field);
		$form->css_classes[] = 'DataTableResponse';
		$array['serveer_status'] = $form->getHtml();

		// editable financien status
		$field = new SelectField('financien_status', $bestelling->financien_status, 'Financien status', HappieFinancienStatus::getSelectOptions());
		$form = new InlineForm($bestelling, 'financienstatus' . $bestelling->bestelling_id, happieUrl . '/financienstatus', $field);
		$form->css_classes[] = 'DataTableResponse';
		$array['financien_status'] = $form->getHtml();

		// editable opmerking
		$field = new TextareaField('opmerking', $bestelling->opmerking, 'Allergie/Opmerking');
		$form = new InlineForm($bestelling, 'opmerking' . $bestelling->bestelling_id, happieUrl . '/opmerking', $field);
		$form->css_classes[] = 'DataTableResponse';
		$array['opmerking'] = $form->getHtml();

		return parent::getJson($array);
	}

}

class HappieBestellingenView extends DataTable {

	public function __construct($dataUrl = '/overzicht', $titel = 'Alle bestellingen', $groupByColumn = 'datum') {
		parent::__construct(HappieBestellingenModel::orm, 'HappieBestellingen', $titel, $groupByColumn);
		$this->dataUrl = happieUrl . $dataUrl;

		$this->addColumn('gang', 'menukaart_item');
		$this->addColumn('menu_groep', 'menukaart_item');

		$this->hideColumn('datum');
		$this->hideColumn('wijzig_historie');
		$this->searchColumn('tafel');
		$this->searchColumn('financien_status');
		$this->searchColumn('menu_groep');
		$this->searchColumn('menukaart_item');

		$wijzig = new DataTableKnop('== 1', $url = happieUrl . '/wijzig', 'post popup TableSelection', 87, 'Wijzig', 'Bestelling wijzigen (Sneltoets: W)', 'DTTT_button_edit');
		$wijzig->data = HappieBestellingenModel::orm . '=#' . $this->tableId;
		$this->addKnop($wijzig);
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
