<?php
namespace Taken\MLT;

require_once 'formulier.class.php';

/**
 * BeheerAbonnementenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle abonnementen en waarschuwingen.
 * 
 */
class BeheerAbonnementenView extends \SimpleHtml {

	private $_leden_abonnementen;
	private $_repetities;
	private $_alleenWaarschuwingen;
	private $_ingeschakeld;
	
	public function __construct($matrix=null, $repetities=null, $alleenWaarschuwingen=false, $ingeschakeld=null) {
		$this->_leden_abonnementen = $matrix;
		$this->_repetities = $repetities;
		$this->_alleenWaarschuwingen = $alleenWaarschuwingen;
		$this->_ingeschakeld = $ingeschakeld;
	}
	
	public function getTitel() {
		return 'Beheer abonnementen';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		$status = 'abo';
		if (is_bool($this->_ingeschakeld)) {
			$status = ($this->_ingeschakeld ? 'in' : 'abo'); // uit
		}
		if ($this->_alleenWaarschuwingen) {
			$status = 'waarschuwing';
		}
		$smarty->assign('toon', $status);
		
		$field = new \LidField('voor_lid', null, "Toon abonnementen van persoon:", 'allepersonen');
		$form = new \Formulier('taken-subform-abos', $GLOBALS['taken_module'] .'/voorlid', array($field));
		$form->cssClass .= ' popup';
		$smarty->assign('form', $form);
		
		if (is_array($this->_leden_abonnementen)) { // matrix van abonnementen
			if (is_array($this->_repetities)) {
				$smarty->assign('melding', $this->getMelding());
				$smarty->assign('kop', $this->getTitel());
				$smarty->display('taken/taken_menu.tpl');
				
				$smarty->assign('aborepetities', MaaltijdRepetitiesModel::getAbonneerbareRepetities());
				$smarty->assign('repetities', $this->_repetities);
				$smarty->assign('matrix', $this->_leden_abonnementen);
				$smarty->display('taken/abonnement/beheer_abonnementen.tpl');
			}
			else { // lijst van abonnementen voor novieten of opgegeven lid of lege array bij error
				echo '<tr id="taken-melding"><td id="taken-melding-veld">'. $this->getMelding() .'</td></tr>';
				foreach ($this->_leden_abonnementen as $uid => $abonnementen) {
					$smarty->assign('uid', $uid);
					$smarty->assign('abonnementen', $abonnementen);
					$smarty->display('taken/abonnement/beheer_abonnement_lijst.tpl');
				}
			}
		}
		else { // abonnement aan/afmelding
			echo '<td id="taken-melding-veld">'. $this->getMelding() .'</td>';
			$smarty->assign('abonnement', $this->_leden_abonnementen);
			$smarty->assign('lidid', $this->_leden_abonnementen->getLidId());
			$smarty->assign('uid', $this->_leden_abonnementen->getLid()->getUid());
			$smarty->display('taken/abonnement/beheer_abonnement_veld.tpl');
		}
	}
}

?>