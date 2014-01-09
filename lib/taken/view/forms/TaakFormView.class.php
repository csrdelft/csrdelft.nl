<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * TaakFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corveetaak.
 * 
 */
class TaakFormView extends \SimpleHtml {

	private $_form;
	private $_tid;
	
	public function __construct($tid, $fid=null, $uid=null, $crid=null, $mid=null, $datum=null, $punten=null, $bonus_malus=null) {
		$this->_tid = $tid;
		
		$functieNamen = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$functiePunten = 'var punten=[];';
		$functieSelectie = array();
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->getFunctieId()] = $functie->getNaam();
			$functiePunten .= 'punten['. $functie->getFunctieId() .']='. $functie->getStandaardPunten() .';';
			if ($fid === $functie->getFunctieId()) {
				$functieSelectie[$fid] = 'arrow';
			}
		}
		
		$formFields['fid'] = new \SelectField('functie_id', $fid, 'Functie', $functieNamen, $functieSelectie);
		$formFields['fid']->setOnChangeScript($functiePunten ."$('#field_standaard_punten').val(punten[this.value]);");
		$formFields['lid'] = new \LidField('lid_id', $uid, 'Lid');
		$formFields['lid']->title = 'Bij het wijzigen van het toegewezen lid worden ook de corveepunten aan het nieuwe lid gegeven.';
		$formFields[] = new \DatumField('datum', $datum, 'Datum', date('Y')+2, date('Y')-2);
		$formFields[] = new \IntField('punten', $punten, 'Punten', 10, 0);
		$formFields[] = new \IntField('bonus_malus', $bonus_malus, 'Bonus/malus', 10, -10);
		$formFields[] = new \HiddenField('crv_repetitie_id', $crid);
		$formFields['mid'] = new \IntField('maaltijd_id', $mid, 'Gekoppelde maaltijd', null, 0, true);
		$formFields['mid']->title = 'Het ID van de maaltijd waar deze taak bij hoort.';
		
		$this->_form = new \Formulier('taken-corveetaak-form', $GLOBALS['taken_module'] .'/opslaan/'. $tid, $formFields);
	}
	
	public function getTitel() {
		if ($this->_tid === 0) {
			return 'Corveetaak aanmaken'; 
		}
		return 'Corveetaak wijzigen'; 
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		if ($this->_tid === 0) {
			$smarty->assign('nocheck', true);
		}
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_tid) || $this->_tid < 0) {
			return false;
		}
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>