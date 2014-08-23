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

	private $repetities;

	public function __construct(array $matrix, $repetities) {
		parent::__construct($matrix, 'Beheer voorkeuren');
		$this->repetities = $repetities;
	}

	public function view() {
		$this->smarty->assign('matrix', $this->model);
		$this->smarty->assign('repetities', $this->repetities);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/voorkeur/beheer_voorkeuren.tpl');
	}

}

class BeheerVoorkeurView extends SmartyTemplateView {

	public function __construct(CorveeVoorkeur $voorkeur) {
		parent::__construct($voorkeur);
	}

	public function view() {
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('crid', $this->model->getCorveeRepetitieId());
		$this->smarty->assign('uid', $this->model->getUid());
		$this->smarty->display('maalcie/voorkeur/beheer_voorkeur_veld.tpl');
	}

}
