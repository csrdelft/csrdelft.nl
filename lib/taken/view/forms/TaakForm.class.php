<?php

/**
 * TaakForm.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corveetaak.
 * 
 */
class TaakForm extends TemplateView {

	private $_form;
	private $_tid;

	public function __construct($tid, $fid = null, $uid = null, $crid = null, $mid = null, $datum = null, $punten = null, $bonus_malus = null) {
		parent::__construct();
		$this->_tid = $tid;

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
		$fields['lid'] = new LidField('lid_id', $uid, 'Naam of lidnummer');
		$fields['lid']->title = 'Bij het wijzigen van het toegewezen lid worden ook de corveepunten aan het nieuwe lid gegeven.';
		$fields[] = new DatumField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new IntField('punten', $punten, 'Punten', 0, 10);
		$fields[] = new IntField('bonus_malus', $bonus_malus, 'Bonus/malus', -10, 10);
		$fields[] = new HiddenField('crv_repetitie_id', $crid);
		$fields['mid'] = new IntField('maaltijd_id', $mid, 'Gekoppelde maaltijd', 0);
		$fields['mid']->title = 'Het ID van de maaltijd waar deze taak bij hoort.';
		$fields[] = new SubmitResetCancel();

		$this->_form = new Formulier(null, 'taken-corveetaak-form', Instellingen::get('taken', 'url') . '/opslaan/' . $tid, $fields);
	}

	public function getTitel() {
		if ($this->_tid === 0) {
			return 'Corveetaak aanmaken';
		}
		return 'Corveetaak wijzigen';
	}

	public function view() {
		$this->_form->addCssClass('popup');
		$this->_form->addCssClass('PreventUnchanged');
		$this->smarty->assign('form', $this->_form);
		if ($this->_tid === 0) {
			$this->smarty->assign('nocheck', true);
		}
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_tid) || $this->_tid < 0) {
			return false;
		}
		$valid = $this->_form->validate();
		$fields = $this->_form->getFields();
		if (is_int($fields['mid']->getValue())) {
			try {
				$maaltijd = MaaltijdenModel::getMaaltijd($fields['mid']->getValue(), true);
			} catch (\Exception $e) {
				$fields['mid']->error = 'Maaltijd bestaat niet.';
				return false;
			}
		}
		return $valid;
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>