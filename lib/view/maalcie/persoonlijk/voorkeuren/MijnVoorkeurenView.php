<?php

namespace CsrDelft\view\maalcie\persoonlijk\voorkeuren;

use CsrDelft\view\maalcie\forms\EetwensForm;
use CsrDelft\view\SmartyTemplateView;


/**
 * MijnVoorkeurenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van voorkeuren die een lid aan of uit kan zetten.
 *
 */
class MijnVoorkeurenView extends SmartyTemplateView {

	public function __construct(array $voorkeuren) {
		parent::__construct($voorkeuren, 'Mijn voorkeuren');
	}

	public function view() {
		$this->smarty->assign('eetwens', new EetwensForm());
		$this->smarty->assign('voorkeuren', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/voorkeur/mijn_voorkeuren.tpl');
	}

}
