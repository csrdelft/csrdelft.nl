<?php

/**
 * MijnAbonnementenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van abonnementen die een lid aan of uit kan zetten.
 * 
 */
class MijnAbonnementenView extends SmartyTemplateView {

	public function __construct($abonnementen) {
		parent::__construct($abonnementen, 'Mijn abonnementen');
	}

	public function view() {
		$this->smarty->assign('abonnementen', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/abonnement/mijn_abonnementen.tpl');
	}

}

class MijnAbonnementView extends SmartyTemplateView {

	public function __construct(MaaltijdAbonnement $abo) {
		parent::__construct($abo);
	}

	public function view() {
		$this->smarty->assign('uid', $this->model->uid);
		$this->smarty->assign('mrid', $this->model->mlt_repetitie_id);
		echo '<td id="maalcie-melding-veld">' . getMelding() . '</td>';
		$this->smarty->display('maalcie/abonnement/mijn_abonnement_veld.tpl');
	}

}
