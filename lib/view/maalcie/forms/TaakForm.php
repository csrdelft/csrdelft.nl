<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * TaakForm.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken corveetaak.
 *
 */
class TaakForm extends ModalForm {

	public function __construct(CorveeTaak $taak, $action) {
		parent::__construct($taak, '/corvee/beheer/' . $action);

		if ($taak->taak_id === null) {
			$this->titel = 'Corveetaak aanmaken';
		} else {
			$this->titel = 'Corveetaak wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$functieNamen = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$functiePunten = 'var punten=[];';
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->functie_id] = $functie->naam;
			$functiePunten .= 'punten[' . $functie->functie_id . ']=' . $functie->standaard_punten . ';';
			if ($taak->punten === null) {
				$taak->punten = $functie->standaard_punten;
			}
		}

		$fields = [];
		$fields['fid'] = new RequiredSelectField('functie_id', $taak->functie_id, 'Functie', $functieNamen);
		$fields['fid']->onchange = $functiePunten . "$('.punten_field').val(punten[this.value]);";
		$fields['lid'] = new LidField('uid', $taak->uid, 'Naam of lidnummer');
		$fields['lid']->title = 'Bij het wijzigen van het toegewezen lid worden ook de corveepunten aan het nieuwe lid gegeven.';
		$fields[] = new RequiredDateField('datum', $taak->datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields['ptn'] = new RequiredIntField('punten', $taak->punten, 'Punten', 0, 10);
		$fields['ptn']->css_classes[] = 'punten_field';
		$fields[] = new RequiredIntField('bonus_malus', $taak->bonus_malus, 'Bonus/malus', -10, 10);
		$fields['crid'] = new IntField('crv_repetitie_id', $taak->crv_repetitie_id, null);
		$fields['crid']->readonly = true;
		$fields['crid']->hidden = true;
		$fields['mid'] = new IntField('maaltijd_id', $taak->maaltijd_id, 'Gekoppelde maaltijd', 0);
		$fields['mid']->title = 'Het ID van de maaltijd waar deze taak bij hoort.';

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

	public function validate() {
		$valid = parent::validate();
		$fields = $this->getFields();
		if (is_numeric($fields['mid']->getValue())) {
			try {
				MaaltijdenModel::instance()->getMaaltijd($fields['mid']->getValue(), true);
			} catch (CsrGebruikerException $e) {
				$fields['mid']->error = 'Maaltijd bestaat niet.';
				$valid = false;
			}
		}
		return $valid;
	}

}
