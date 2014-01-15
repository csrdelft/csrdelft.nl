<?php



/**
 * TaakFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corveetaak.
 * 
 */
class TaakFormView extends TemplateView {

	private $_form;
	private $_tid;

	public function __construct($tid, $fid = null, $uid = null, $crid = null, $mid = null, $datum = null, $punten = null, $bonus_malus = null) {
		parent::__construct();
		$this->_tid = $tid;

		$functieNamen = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$functiePunten = 'var punten=[];';
		$functieSelectie = array();
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->getFunctieId()] = $functie->getNaam();
			$functiePunten .= 'punten[' . $functie->getFunctieId() . ']=' . $functie->getStandaardPunten() . ';';
			if ($punten === null) {
				$punten = $functie->getStandaardPunten();
			}
			if ($fid === $functie->getFunctieId()) {
				$functieSelectie[$fid] = 'arrow';
			}
		}

		$formFields['fid'] = new SelectField('functie_id', $fid, 'Functie', $functieNamen, $functieSelectie);
		$formFields['fid']->onchange = $functiePunten . "$('#field_punten').val(punten[this.value]);";
		$formFields['lid'] = new LidField('lid_id', $uid, 'Naam of lidnummer');
		$formFields['lid']->title = 'Bij het wijzigen van het toegewezen lid worden ook de corveepunten aan het nieuwe lid gegeven.';
		$formFields[] = new DatumField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$formFields[] = new IntField('punten', $punten, 'Punten', 10, 0);
		$formFields[] = new IntField('bonus_malus', $bonus_malus, 'Bonus/malus', 10, -10);
		$formFields[] = new HiddenField('crv_repetitie_id', $crid);
		$formFields['mid'] = new IntField('maaltijd_id', $mid, 'Gekoppelde maaltijd', null, 0, true);
		$formFields['mid']->title = 'Het ID van de maaltijd waar deze taak bij hoort.';

		$this->_form = new Formulier('taken-corveetaak-form', Instellingen::get('taken', 'url') . '/opslaan/' . $tid, $formFields);
	}

	public function getTitel() {
		if ($this->_tid === 0) {
			return 'Corveetaak aanmaken';
		}
		return 'Corveetaak wijzigen';
	}

	public function view() {
		$this->smarty->assign('melding', $this->getMelding());
		$this->smarty->assign('kop', $this->getTitel());
		$this->_form->css_classes[] = 'popup';
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
		if ($fields['mid']->getValue() !== 0) {
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