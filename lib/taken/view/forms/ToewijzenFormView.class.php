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

		$fields[] = new LidField('lid_id', $taak->getLidId(), 'Naam of lidnummer', 'leden');

		$this->_form = new Formulier('taken-taak-toewijzen-form', Instellingen::get('taken', 'url') . '/toewijzen/' . $this->_taak->getTaakId(), $fields);
	}

	public function getTitel() {
		return 'Taak toewijzen aan lid';
	}

	public function getLidLink($uid) {
		$lid = \LidCache::getLid($uid);
		if ($lid instanceof \Lid) {
			return $lid->getNaamLink(Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'));
		}
		return $uid;
	}

	public function getIsJongsteLichting($uid) {
		return ($this->_jong === \LidCache::getLid($uid)->getLichting());
	}

	public function view() {
		$this->_form->css_classes[] = 'popup';

		$this->smarty->assign('taak', $this->_taak);
		$this->smarty->assign('suggesties', $this->_suggesties);

		$crid = $this->_taak->getCorveeRepetitieId();
		if ($crid !== null) {
			$this->smarty->assign('voorkeurbaar', CorveeRepetitiesModel::getRepetitie($crid)->getIsVoorkeurbaar());
		}
		if ($this->_taak->getCorveeFunctie()->getIsKwalificatieBenodigd()) {
			$this->smarty->assign('voorkeur', Instellingen::get('corvee', 'suggesties_voorkeur_kwali_filter'));
			$this->smarty->assign('recent', Instellingen::get('corvee', 'suggesties_recent_kwali_filter'));
		} else {
			$this->smarty->assign('voorkeur', Instellingen::get('corvee', 'suggesties_voorkeur_filter'));
			$this->smarty->assign('recent', Instellingen::get('corvee', 'suggesties_recent_filter'));
		}

		$lijst = $this->smarty->fetch('taken/corveetaak/suggesties_lijst.tpl');
		$fields[] = new HtmlComment($lijst);
		$fields[] = new SubmitResetCancel();
		$this->_form->addFields($fields);

		$this->smarty->assign('form', $this->_form);
		$this->smarty->display('taken/popup_form.tpl');
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