<?php

require_once 'maalcie/model/MaaltijdenModel.class.php';

/**
 * TaakForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken corveetaak.
 * 
 */
class TaakForm extends ModalForm {

	public function __construct($tid, $fid = null, $uid = null, $crid = null, $mid = null, $datum = null, $punten = null, $bonus_malus = null) {
		parent::__construct($crid, 'maalcie-corveetaak-form', Instellingen::get('taken', 'url') . '/opslaan/' . $tid);

		if (!is_int($tid) || $tid < 0) {
			throw new Exception('invalid tid');
		}
		if ($tid === 0) {
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
			if ($punten === null) {
				$punten = $functie->standaard_punten;
			}
		}

		$fields['fid'] = new SelectField('functie_id', $fid, 'Functie', $functieNamen);
		$fields['fid']->onchange = $functiePunten . "$('#field_punten').val(punten[this.value]);";
		$fields['lid'] = new LidField('uid', $uid, 'Naam of lidnummer');
		$fields['lid']->title = 'Bij het wijzigen van het toegewezen lid worden ook de corveepunten aan het nieuwe lid gegeven.';
		$fields[] = new DatumField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new IntField('punten', $punten, 'Punten', 0, 10);
		$fields[] = new IntField('bonus_malus', $bonus_malus, 'Bonus/malus', -10, 10);
		$fields['crid'] = new IntField('crv_repetitie_id', $crid, null);
		$fields['crid']->hidden = true;
		$fields['mid'] = new IntField('maaltijd_id', $mid, 'Gekoppelde maaltijd', 0);
		$fields['mid']->title = 'Het ID van de maaltijd waar deze taak bij hoort.';
		$fields[] = new FormButtons();

		$this->addFields($fields);
	}

	public function validate() {
		$valid = parent::validate();
		$fields = $this->getFields();
		if (is_int($fields['mid']->getValue())) {
			try {
				MaaltijdenModel::getMaaltijd($fields['mid']->getValue(), true);
			} catch (Exception $e) {
				$fields['mid']->error = 'Maaltijd bestaat niet.';
				$valid = false;
			}
		}
		// wijzigen van verborgen veld mag niet
		if ($this->getModel() !== $this->findByName('crv_repetitie_id')->getValue()) {
			return false;
		}
		return $valid;
	}

}
