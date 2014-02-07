<?php



/**
 * BeheerAbonnementenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle abonnementen en waarschuwingen.
 * 
 */
class BeheerAbonnementenView extends TemplateView {

	private $_leden_abonnementen;
	private $_repetities;
	private $_alleenWaarschuwingen;
	private $_ingeschakeld;

	public function __construct($matrix = null, $repetities = null, $alleenWaarschuwingen = false, $ingeschakeld = null) {
		parent::__construct();
		$this->_leden_abonnementen = $matrix;
		$this->_repetities = $repetities;
		$this->_alleenWaarschuwingen = $alleenWaarschuwingen;
		$this->_ingeschakeld = $ingeschakeld;
	}

	public function getTitel() {
		return 'Beheer abonnementen';
	}

	public function view() {
		$status = 'abo';
		if (is_bool($this->_ingeschakeld)) {
			$status = ($this->_ingeschakeld ? 'in' : 'abo'); // uit
		}
		if ($this->_alleenWaarschuwingen) {
			$status = 'waarschuwing';
		}
		$this->smarty->assign('toon', $status);

		$field = new LidField('voor_lid', null, "Toon abonnementen van persoon:", 'allepersonen');
		$form = new Formulier('taken-subform-abos', Instellingen::get('taken', 'url') . '/voorlid', array($field));
		$form->css_classes[] = 'popup';
		$this->smarty->assign('form', $form);

		if (is_array($this->_leden_abonnementen)) { // matrix van abonnementen
			if (is_array($this->_repetities)) {
				$this->smarty->display('taken/menu_pagina.tpl');

				$this->smarty->assign('aborepetities', MaaltijdRepetitiesModel::getAbonneerbareRepetities());
				$this->smarty->assign('repetities', $this->_repetities);
				$this->smarty->assign('matrix', $this->_leden_abonnementen);
				$this->smarty->display('taken/abonnement/beheer_abonnementen.tpl');
			} else { // lijst van abonnementen voor novieten of opgegeven lid of lege array bij error
				echo '<tr id="taken-melding"><td id="taken-melding-veld">' . $this->getMelding() . '</td></tr>';
				foreach ($this->_leden_abonnementen as $uid => $abonnementen) {
					$this->smarty->assign('uid', $uid);
					$this->smarty->assign('abonnementen', $abonnementen);
					$this->smarty->display('taken/abonnement/beheer_abonnement_lijst.tpl');
				}
			}
		} else { // abonnement aan/afmelding
			echo '<td id="taken-melding-veld">' . $this->getMelding() . '</td>';
			$this->smarty->assign('abonnement', $this->_leden_abonnementen);
			$this->smarty->assign('lidid', $this->_leden_abonnementen->getLidId());
			$this->smarty->assign('uid', $this->_leden_abonnementen->getLid()->getUid());
			$this->smarty->display('taken/abonnement/beheer_abonnement_veld.tpl');
		}
	}

}

?>