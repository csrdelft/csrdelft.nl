<?php

/**
 * BeheerVoorkeurenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle voorkeuren van alle leden.
 * 
 */
class BeheerVoorkeurenView extends SmartyTemplateView {

	public function __construct(array $matrix, $repetities) {
		parent::__construct($matrix, 'Beheer voorkeuren');
		$this->smarty->assign('matrix', $matrix);
		$this->smarty->assign('repetities', $repetities);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/voorkeur/beheer_voorkeuren.tpl');
	}

}

class BeheerVoorkeurView extends SmartyTemplateView {

	public function __construct(CorveeVoorkeur $voorkeur) {
		parent::__construct($voorkeur);
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('crid', $voorkeur->getCorveeRepetitieId());
		$this->smarty->assign('uid', $voorkeur->getUid());
	}

	public function view() {
		$this->smarty->display('maalcie/voorkeur/beheer_voorkeur_veld.tpl');
	}

}
