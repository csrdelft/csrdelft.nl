<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * TaakToewijzenFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 * 
 */
class TaakToewijzenFormView extends \SimpleHtml {

	private $_form;
	private $_tid;
	
	public function __construct(CorveeTaak $taak, array $leden_punten, array $voorkeuren) {
		$this->_tid = $taak->getTaakId();
		$kwali = $taak->getCorveeFunctie()->getIsKwalificatieBenodigd();
		$suggesties = array();
		foreach ($leden_punten as $uid => $puntenlijst) {
			$lid = \LidCache::getLid($uid);
			if ($kwali) {
				$suggesties[$uid] = '# '. $puntenlijst['aantal'][$taak->getFunctieId()];
			}
			else {
				$suggesties[$uid] = $puntenlijst['prognose'];
			}
			$suggesties[$uid] .= ' : '. $lid->getNaamLink('civitas', 'plain');
		}
		$cssClass = 'smile';
		$css = array();
		foreach ($voorkeuren as $voorkeur) {
			$uid = $voorkeur->getLidId();
			if (array_key_exists($uid, $suggesties)) {
				$css[$uid] = $cssClass;
			}
		}
		
		$formFields[] = new \LidField('lid_id', $taak->getLidId(), 'Naam of lidnummer', 'leden');
		$formFields['sug'] = new \SelectField('suggesties', null, 'Suggesties (gesorteerd op puntenprognose)', $suggesties, $css, 10);
		$formFields['sug']->inputClasses[] = 'multiple';
		$formFields['sug']->setOnChangeScript("document.getElementById('field_lid_id').value=this.value;");
		$formFields['vrk'] = new \KeuzeRondjeField('keuze', 'alles', 'Toon in suggestielijst', array(
			'alles' => 'Alle '. sizeof($leden_punten) . ($kwali ? ' gekwalificeerde' : '') .' leden',
			'voorkeur' => 'Alleen '. sizeof($css) .' met voorkeur'));
		$formFields['vrk']->setOnChangeScript("$('#field_suggesties option:not(.". $cssClass .")').toggle();");
		if (empty($css)) {
			$formFields['vrk']->disabled = true;
		}
		
		$this->_form = new \Formulier('taken-taak-toewijzen-form', '/actueel/taken/corveebeheer/toewijzen/'. $this->_tid, $formFields);
	}
	
	public function getTitel() {
		return 'Taak toewijzen';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_tid) || $this->_tid <= 0) {
			return false;
		}
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>