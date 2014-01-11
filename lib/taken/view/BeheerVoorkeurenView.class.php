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
				$this->assign('voorkeuren', $this->_leden_voorkeuren);
				$this->display('taken/voorkeur/beheer_voorkeur_lijst.tpl');
			} else { // voorkeur aan/afmelding
				$this->assign('voorkeur', $this->_leden_voorkeuren);
				$this->assign('crid', $this->_leden_voorkeuren->getCorveeRepetitieId());
				$this->assign('uid', $this->_leden_voorkeuren->getLidId());
				$this->display('taken/voorkeur/beheer_voorkeur_veld.tpl');
			}
		} else { // matrix of repetities and voorkeuren
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->display('taken/menu_pagina.tpl');

			$this->assign('matrix', $this->_leden_voorkeuren);
			$this->assign('repetities', $this->_repetities);
			$this->display('taken/voorkeur/beheer_voorkeuren.tpl');
		}
	}

}

?>