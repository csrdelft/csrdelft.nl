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
		$this->assign('mededelingen_root', MededelingenContent::mededelingenRoot);
		$this->display('mededelingen/top3overzicht.tpl');
	}

}
