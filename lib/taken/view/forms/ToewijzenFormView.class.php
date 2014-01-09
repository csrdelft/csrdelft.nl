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
		$lid = \LidCache::getLid($uid);
		if ($lid instanceof \Lid) {
			return $lid->getNaamLink($GLOBALS['weergave_ledennamen_beheer'], $GLOBALS['weergave_ledennamen']);
		}
		return $uid;
	}
	
	public function getIsJongsteLichting($uid) {
		return ($this->_jong === \LidCache::getLid($uid)->getLichting());
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		
		$smarty->assignByRef('this', $this);
		$smarty->assign('taak', $this->_taak);
		$smarty->assign('suggesties', $this->_suggesties);
		
		$crid = $this->_taak->getCorveeRepetitieId();
		if ($crid !== null) {
			$smarty->assign('voorkeurbaar', CorveeRepetitiesModel::getRepetitie($crid)->getIsVoorkeurbaar());
		}
		if ($this->_taak->getCorveeFunctie()->getIsKwalificatieBenodigd()) {
			$smarty->assign('voorkeur', $GLOBALS['suggesties_voorkeur_kwali_filter']);
			$smarty->assign('recent', $GLOBALS['suggesties_recent_kwali_filter']);
		}
		else {
			$smarty->assign('voorkeur', $GLOBALS['suggesties_voorkeur_filter']);
			$smarty->assign('recent', $GLOBALS['suggesties_recent_filter']);
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
		return $this->_form->valid();
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>