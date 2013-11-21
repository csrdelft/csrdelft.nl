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
	
	public function __construct(CorveeTaak $taak, array $leden_punten, array $voorkeuren, $repetitie) {
		$this->_tid = $taak->getTaakId();
		$kwali = $taak->getCorveeFunctie()->getIsKwalificatieBenodigd();
		$suggestielijst = array();
		foreach ($leden_punten as $uid => $puntenlijst) {
			$lid = \LidCache::getLid($uid);
			if ($kwali) {
				$suggestielijst[$uid] = '# '. $puntenlijst['aantal'][$taak->getFunctieId()];
			}
			else {
				$suggestielijst[$uid] = $puntenlijst['prognose'];
			}
			$suggestielijst[$uid] .= ' : '. $lid->getNaamLink($GLOBALS['weergave_ledennamen_beheer'], 'plain');
		}
		$voorkeurCssClass = 'smile';
		$voorkeurlijst = array();
		// Als een lid een voorkeur heeft, maar niet voorkomt in de lijst van suggesties
		// kan het zijn dat er een kwalificatie benodigd is die dat lid niet heeft.
		// De voorkeur wordt dan niet getoont en ook niet meegenomen in de telling.
		foreach ($voorkeuren as $voorkeur) {
			$uid = $voorkeur->getLidId();
			if (array_key_exists($uid, $suggestielijst)) {
				$voorkeurlijst[$uid] = $voorkeurCssClass;
			}
		}
		
		$formFields[] = new \LidField('lid_id', $taak->getLidId(), 'Naam of lidnummer', 'leden');
		$formFields['sug'] = new \SelectField('suggesties', null, 'Suggesties (gesorteerd op puntenprognose)', $suggestielijst, $voorkeurlijst, 10);
		$formFields['sug']->inputClasses[] = 'multiple';
		$formFields['sug']->setOnChangeScript("document.getElementById('field_lid_id').value=this.value;");
		
		if ($repetitie === null) {
			$formFields[] = new \HTMLComment('<br />Dit is geen periodieke taak dus zijn er geen voorkeuren.');
		}
		else {
			$formFields['vrk'] = new \KeuzeRondjeField('keuze', 'alles', 'Toon in suggestielijst', array(
				'alles' => 'Alle '. sizeof($leden_punten) . ($kwali ? ' gekwalificeerde' : '') .' leden',
				'voorkeur' => 'Alleen '. sizeof($voorkeurlijst) .' met voorkeur voor: '. $repetitie->getCorveeFunctie()->getNaam() .' op '. $repetitie->getDagVanDeWeekText()));
			$formFields['vrk']->setOnChangeScript("$('#field_suggesties option:not(.". $voorkeurCssClass .")').toggle();");
			if (empty($voorkeurlijst)) {
				$formFields['vrk']->disabled = true;
			}
		}
		
		$this->_form = new \Formulier('taken-taak-toewijzen-form', '/actueel/taken/corveebeheer/toewijzen/'. $this->_tid, $formFields);
	}
	
	public function getTitel() {
		return 'Taak toewijzen aan lid';
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