<?php

require_once 'mededelingencontent.class.php';

class MededelingTopDrieOverzichtContent extends TemplateView {

	public function __construct() {
		parent::__construct();
	}

	public function getTitel() {
		return 'Top 3 mededelingenoverzicht';
	}

	public function view() {
		$this->smarty->assign('mededelingen_root', MededelingenContent::mededelingenRoot);
		$this->smarty->display('mededelingen/top3overzicht.tpl');
	}

}
