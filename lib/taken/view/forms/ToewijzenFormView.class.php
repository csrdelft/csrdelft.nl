<?php



/**
 * ToewijzenFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 * 
 */
class ToewijzenFormView extends TemplateView {

	private $_form;
	private $_taak;
	private $_suggesties;
	private $_jong;

	public function __construct(CorveeTaak $taak, array $suggesties) {
		parent::__construct();
		$this->_taak = $taak;
		$this->_suggesties = $suggesties;
		$this->_jong = (int) \Lichting::getJongsteLichting();

		$formFields[] = new LidField('lid_id', $taak->getLidId(), 'Naam of lidnummer', 'leden');

		$this->_form = new Formulier('taken-taak-toewijzen-form', $GLOBALS['taken_module'] . '/toewijzen/' . $this->_taak->getTaakId(), $formFields);
	}

	public function getTitel() {
		return 'Taak toewijzen aan lid';
	}

	public function getLidLink($uid) {
		$lid = \LidCache::getLid($uid);
		if ($lid instanceof \Lid) {
			return $lid->getNaamLink($GLOBALS['corvee']['weergave_ledennamen_beheer'], $GLOBALS['corvee']['weergave_ledennamen']);
		}
		return $uid;
	}

	public function getIsJongsteLichting($uid) {
		return ($this->_jong === \LidCache::getLid($uid)->getLichting());
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->_form->css_classes[] = 'popup';

		$this->assignByRef('this', $this);
		$this->assign('taak', $this->_taak);
		$this->assign('suggesties', $this->_suggesties);

		$crid = $this->_taak->getCorveeRepetitieId();
		if ($crid !== null) {
			$this->assign('voorkeurbaar', CorveeRepetitiesModel::getRepetitie($crid)->getIsVoorkeurbaar());
		}
		if ($this->_taak->getCorveeFunctie()->getIsKwalificatieBenodigd()) {
			$this->assign('voorkeur', $GLOBALS['corvee']['suggesties_voorkeur_kwali_filter']);
			$this->assign('recent', $GLOBALS['corvee']['suggesties_recent_kwali_filter']);
		} else {
			$this->assign('voorkeur', $GLOBALS['corvee']['suggesties_voorkeur_filter']);
			$this->assign('recent', $GLOBALS['corvee']['suggesties_recent_filter']);
		}

		$lijst = $this->fetch('taken/corveetaak/suggesties_lijst.tpl');
		$formFields[] = new HtmlComment($lijst);
		$this->_form->addFields($formFields);

		$this->assign('form', $this->_form);
		$this->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_taak->getTaakId()) || $this->_taak->getTaakId() <= 0) {
			return false;
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>