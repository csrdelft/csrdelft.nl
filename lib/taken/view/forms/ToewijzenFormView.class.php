<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * ToewijzenFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 * 
 */
class ToewijzenFormView extends \SimpleHtml {

	private $_form;
	private $_taak;
	private $_suggesties;
	private $_jong;
	
	public function __construct(CorveeTaak $taak, array $suggesties) {
		$this->_taak = $taak;
		$this->_suggesties = $suggesties;
		$this->_jong = (int) \Lichting::getJongsteLichting();
		
		$formFields[] = new \LidField('lid_id', $taak->getLidId(), 'Naam of lidnummer', 'leden');
		
		$this->_form = new \Formulier('taken-taak-toewijzen-form', $GLOBALS['taken_module'] .'/toewijzen/'. $this->_taak->getTaakId(), $formFields);
	}
	
	public function getTitel() {
		return 'Taak toewijzen aan lid';
	}
	
	public function getLidLink($uid) {
		return \LidCache::getLid($uid)->getNaamLink($GLOBALS['weergave_ledennamen_beheer'], 'link');
	}
	
	public function getIsJongsteLichting($uid) {
		return ($this->_jong === (int) \LidCache::getLid($uid)->getLichting());
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		
		$smarty->assign('this', $this);
		$smarty->assign('taak', $this->_taak);
		$smarty->assign('suggesties', $this->_suggesties);
		
		if ($this->_taak->getCorveeRepetitieId() !== null) {
			$repetitie = CorveeRepetitiesModel::getRepetitie($this->_taak->getCorveeRepetitieId());
			$smarty->assign('voorkeur', $repetitie->getIsVoorkeurbaar());
		}
		
		$lijst = $smarty->fetch('taken/corveetaak/suggesties_lijst.tpl');
		$formFields[] = new \HTMLComment($lijst);
		$this->_form->addFields($formFields);
		
		$smarty->assign('form', $this->_form);
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_taak->getTaakId()) || $this->_taak->getTaakId() <= 0) {
			return false;
		}
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>