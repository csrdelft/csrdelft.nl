<?php

/**
 * MijnAbonnementenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van abonnementen die een lid aan of uit kan zetten.
 * 
 */
class MijnAbonnementenView extends TemplateView {

	private $_abonnementen;

	public function __construct($abonnementen) {
		parent::__construct();
		$this->_abonnementen = $abonnementen;
	}

	public function getTitel() {
		return 'Mijn abonnementen';
	}

	public function view() {
		if (is_array($this->_abonnementen)) { // list of abonnementen
			$this->smarty->display('taken/menu_pagina.tpl');

			$this->smarty->assign('abonnementen', $this->_abonnementen);
			$this->smarty->display('taken/abonnement/mijn_abonnementen.tpl');
		} elseif (is_int($this->_abonnementen)) { // id of disabled abonnement
			echo '<td id="taken-melding-veld">' . SimpleHTML::getMelding() . '</td>';
			$this->smarty->assign('mrid', $this->_abonnementen);
			$this->smarty->display('taken/abonnement/mijn_abonnement_veld.tpl');
		} else { // single abonnement
			echo '<td id="taken-melding-veld">' . SimpleHTML::getMelding() . '</td>';
			$this->smarty->assign('uid', $this->_abonnementen->getLidId());
			$this->smarty->assign('mrid', $this->_abonnementen->getMaaltijdRepetitieId());
			$this->smarty->display('taken/abonnement/mijn_abonnement_veld.tpl');
		}
	}

}

?>