<?php

/**
 * BeheerVoorkeurenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle voorkeuren van alle leden.
 * 
 */
class BeheerVoorkeurenView extends TemplateView {

	private $_leden_voorkeuren;
	private $_repetities;

	public function __construct($leden_voorkeuren, $repetities = null) {
		parent::__construct();
		$this->_leden_voorkeuren = $leden_voorkeuren;
		$this->_repetities = $repetities;
	}

	public function getTitel() {
		return 'Beheer voorkeuren';
	}

	public function view() {
		if ($this->_repetities === null) { // voor een lid
			if (is_array($this->_leden_voorkeuren)) { // lijst van voorkeuren
				$this->smarty->assign('voorkeuren', $this->_leden_voorkeuren);
				$this->smarty->display('taken/voorkeur/beheer_voorkeur_lijst.tpl');
			} else { // voorkeur aan/afmelding
				$this->smarty->assign('voorkeur', $this->_leden_voorkeuren);
				$this->smarty->assign('crid', $this->_leden_voorkeuren->getCorveeRepetitieId());
				$this->smarty->assign('uid', $this->_leden_voorkeuren->getLidId());
				$this->smarty->display('taken/voorkeur/beheer_voorkeur_veld.tpl');
			}
		} else { // matrix of repetities and voorkeuren
			$this->smarty->assign('melding', $this->getMelding());
			$this->smarty->assign('kop', $this->getTitel());
			$this->smarty->display('taken/menu_pagina.tpl');

			$this->smarty->assign('matrix', $this->_leden_voorkeuren);
			$this->smarty->assign('repetities', $this->_repetities);
			$this->smarty->display('taken/voorkeur/beheer_voorkeuren.tpl');
		}
	}

}

?>