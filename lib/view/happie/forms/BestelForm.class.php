<?php

require_once 'model/entity/happie/HappieGang.enum.php';

/**
 * BestelForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bestelformulier voor complete menukaart.
 * 
 */
class HappieBestellingWijzigenForm extends ModalForm {

	public function __construct(HappieBestelling $bestelling) {
		parent::__construct($bestelling, get_class($this), happieUrl . '/wijzig', 'Bestelling wijzigen');
		$fields[] = new ObjectIdField($bestelling);

		$fields[] = new HtmlComment('<div class="InputField"><label>Datum</label>' . $bestelling->datum . '</div>');
		$fields[] = new HtmlComment('<div class="InputField"><label>Laatst gewijzigd</label>' . reldate($bestelling->laatst_gewijzigd) . '</div>');

		$fields[] = new SelectField('tafel', null, 'Tafel', range(0, 99)); // array index starts from 0
		// maak invoerveld voor elk item per groep
		$menukaart = HappieMenukaartItemsModel::instance()->getMenukaart();
		$options = array();
		$beschikbaar = false;
		foreach ($menukaart as $gang) {
			foreach ($gang as $groep) {
				foreach ($groep->getItems() as $item) {
					$options[$groep->naam][$item->item_id] = $item->naam;
					if ($item->item_id == $bestelling->menukaart_item) {
						$beschikbaar = min($item->aantal_beschikbaar, $groep->aantal_beschikbaar);
					}
				}
			}
		}
		if ($beschikbaar === false) {
			$options['VERWIJDERD'][$bestelling->menukaart_item] = $bestelling->menukaart_item;
		}
		$fields[] = new SelectField('menukaart_item', $bestelling->menukaart_item, 'Menukaart item', $options, true);

		$fields[] = new IntField('aantal', $bestelling->aantal, 'Aantal', 0, $bestelling->aantal + $beschikbaar);
		$fields[] = new IntField('aantal_geserveerd', $bestelling->aantal_geserveerd, 'Aantal geserveerd', 0, $bestelling->aantal_geserveerd + $beschikbaar); // meer vrijheid dan $bestelling->aantal

		$fields[] = new SelectField('serveer_status', $bestelling->serveer_status, 'Serveer status', HappieServeerStatus::getSelectOptions());
		$fields[] = new SelectField('financien_status', $bestelling->financien_status, 'Financien status', HappieFinancienStatus::getSelectOptions());

		$fields[] = new TextareaField('opmerking', $bestelling->opmerking, 'Allergie/Opmerking');
		$fields[] = new FormDefaultKnoppen(HTTP_REFERER);

		$this->addFields($fields);
	}

}

class HappieBestelForm extends TabsForm {

	public function __construct() {
		parent::__construct(null, get_class($this), happieUrl . '/nieuw', 'Nieuwe bestelling');
		$this->hoverintent = true;

		// tafel invoer
		$table = new SelectField('tafel', null, 'Tafel', range(0, 99)); // array index starts from 0
		$fields[] = $table;

		$fields['k'] = new FormDefaultKnoppen(happieUrl . '/serveer', false);
		$fields['k']->confirmAll();
		$fields['k']->css_classes[] = 'float-right';
		$this->addFields($fields, 'head');

		// maak invoerveld voor elk menukaart-item
		$menukaart = HappieMenukaartItemsModel::instance()->getMenukaart();
		foreach ($menukaart as $gang => $groepen) {

			// drank groepen standaard ingeklapt
			$drank = $gang == HappieGang::Drank;

			// groepeer items
			foreach ($groepen as $groep_id => $groep) {

				// voeg groep toe aan tab en maak tab voor elke gang
				$kopje = new CollapsableSubkopje($groep_id, $groep->naam, $drank);
				$this->addFields(array($kopje), $groep->gang);

				foreach ($groep->getItems() as $item) {
					$fields = array();

					// werkomheen label line-wrap met css style
					$fields[] = new HtmlComment('<div id="item_' . $item->item_id . '" class="alternate-bgcolor" style="display:inline-block;width:330px;">');

					// preload bestelling aantal
					if (isset($bestellingen[$item->item_id])) {
						$aantal = $bestellingen[$item->item_id]->aantal;
						$opmerking = $bestellingen[$item->item_id]->opmerking;
					} else {
						$aantal = 0;
						$opmerking = '';
					}
					$beschikbaar = min($item->aantal_beschikbaar, $groep->aantal_beschikbaar);

					$int = new IntField('item' . $item->item_id, $aantal, $item->naam, 0, $beschikbaar);
					$int->min_alert = false;
					$int->max_alert = 'Te weinig beschikbaar!';
					$fields[] = $int;

					if ($beschikbaar > 0 OR $aantal > 0) {
						$html = '<div id="toggle_' . $item->item_id . '" class="btn toggle-info float-left" style="margin-right:5px;width:32px;padding:0;"><img src="' . CSR_PICS . '/famfamfam/information.png" class="icon" width="16" height="16"></div>';
					} else {
						$html = '<div id="toggle_' . $item->item_id . '" class="float-left alert alert-warning" style="margin-right:5px;width:32px;padding:0;text-align:center;">OP</div>';
					}
					$html .= '<div id="info_' . $item->item_id . '" class="info-data clear-left" style="display:none;">';
					if (!empty($item->allergie_info)) {
						$html .= '<div class="inline alert alert-info" style="margin-right:5px;padding:0 .3em;">' . $item->allergie_info . '</div>';
					}
					$fields[] = new HtmlComment($html . <<<HTML
	<div class="inline float-right" style="margin-right:50px;">{$item->getPrijsFormatted()}</div>
</div>
<div id="expand_{$item->item_id}" style="display:none;">
HTML
					);
					$opm = new TextareaField('opmerking' . $item->item_id, $opmerking, null);
					$opm->placeholder = 'Allergie van klant / opmerking';
					$fields[] = $opm;

					$fields[] = new HtmlComment('<div class="beschrijving" style="font-style:italic;">' . $item->beschrijving . '</div></div></div>'); // close expanded item

					$this->javascript .= <<<JS

$('#{$opm->getId()}').height('30px');
$('#toggle_{$item->item_id}').prependTo('#wrapper_{$int->getId()}').click(function() {
	$('#expand_{$item->item_id}').toggle().find('textarea:first').focus();
	var toggle = $('#expand_{$item->item_id}').is(':visible');
	var show = $('.toggle-allergie:first').prop('data-show');
	if (!show) {
		$('#info_{$item->item_id}').toggle(toggle);
	}
});
JS;
					$this->addFields($fields, $groep->gang);
				}

				$kopje = new HtmlComment('</div>'); // close expanded groep
				$this->addFields(array($kopje), $groep->gang);
			}
		}

		$fields = array();

		$fields['k'] = new FormDefaultKnoppen(happieUrl, false);
		$fields['k']->confirmAll();
		$this->addFields($fields, 'foot');

		$allergie = new FormulierKnop(null, 'toggle-allergie', 'Allergie-info', 'Toon allergie informatie', '/famfamfam/information.png');
		$fields['k']->addKnop($allergie, true);

		$this->javascript .= <<<JS

$('.Formulier label').css('width', '45%');
$('#wrapper_{$table->getId()} label').css('width', '20%');
$('.expanded-submenu').css({
	"-webkit-column-gap": 0,
	"-moz-column-gap": 0,
	"column-gap": 0,
	"-webkit-column-width": "330px",
	"-moz-column-width": "330px",
	"column-width": "330px"
});
$('.FormDefaultKnoppen:first').appendTo('.InputField:first').removeClass('clear-left');
$('.toggle-allergie:first').click(function() {
	var show = $(this).prop('data-show');
	if (!show) {
		$('.info-data').show();
	} else {
		$('.info-data').hide();
	}
	$(this).prop('data-show', !show);
});
JS;
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
