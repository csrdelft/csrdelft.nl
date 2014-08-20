<?php

/**
 * MijnAbonnementenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van abonnementen die een lid aan of uit kan zetten.
 * 
 */
class MijnAbonnementenView extends TemplateView {

	public function __construct($abonnementen) {
		parent::__construct($abonnementen, 'Mijn abonnementen');
		$this->smarty->assign('abonnementen', $this->model);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/abonnement/mijn_abonnementen.tpl');
	}

}

class MijnAbonnementView extends TemplateView {

	public function __construct(MaaltijdAbonnement $abo) {
		parent::__construct($abo);
		$this->smarty->assign('uid', $abo->getUid());
		$this->smarty->assign('mrid', $abo->getMaaltijdRepetitieId());
	}

	public function view() {
		echo '<td id="maalcie-melding-veld">' . SimpleHTML::getMelding() . '</td>';
		$this->smarty->display('maalcie/abonnement/mijn_abonnement_veld.tpl');
	}

}
