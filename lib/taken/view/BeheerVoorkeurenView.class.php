<?php

/**
 * BeheerVoorkeurenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle voorkeuren van alle leden.
 * 
 */
class BeheerVoorkeurenView extends TemplateView {

	public function __construct(array $matrix, $repetities) {
		parent::__construct($matrix, 'Beheer voorkeuren');
		$this->smarty->assign('matrix', $matrix);
		$this->smarty->assign('repetities', $repetities);
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('taken/voorkeur/beheer_voorkeuren.tpl');
	}

}

class BeheerVoorkeurView extends TemplateView {

	public function __construct(CorveeVoorkeur $voorkeur) {
		parent::__construct($voorkeur);
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('crid', $voorkeur->getCorveeRepetitieId());
		$this->smarty->assign('uid', $voorkeur->getLidId());
	}

	public function view() {
		$this->smarty->display('taken/voorkeur/beheer_voorkeur_veld.tpl');
	}

}
