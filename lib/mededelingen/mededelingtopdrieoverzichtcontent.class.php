<?php

require_once 'mededelingencontent.class.php';

class MededelingTopDrieOverzichtContent extends TemplateView {

	public function __construct() {
		parent::__construct();
	}

	public function view() {
		$this->assign('mededelingen_root', MededelingenContent::mededelingenRoot);
		$this->display('mededelingen/top3overzicht.tpl');
	}

}
