<?php

require_once 'maalcie/view/forms/EetwensForm.class.php';

/**
 * MijnVoorkeurenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van voorkeuren die een lid aan of uit kan zetten.
 * 
 */
class MijnVoorkeurenView extends SmartyTemplateView {

	public function __construct(array $voorkeuren) {
		parent::__construct($voorkeuren, 'Mijn voorkeuren');
		$this->smarty->assign('eetwens', new EetwensForm());
		$this->smarty->assign('voorkeuren', $this->model);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/voorkeur/mijn_voorkeuren.tpl');
	}

}

class MijnVoorkeurView extends SmartyTemplateView {

	public function __construct(CorveeVoorkeur $voorkeur) {
		parent::__construct($voorkeur);
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('uid', $voorkeur->getUid());
		$this->smarty->assign('crid', $voorkeur->getCorveeRepetitieId());
	}

	public function view() {
		$this->smarty->display('maalcie/voorkeur/mijn_voorkeur_veld.tpl');
	}

}
