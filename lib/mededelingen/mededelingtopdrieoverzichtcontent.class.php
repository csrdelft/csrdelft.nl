<?php

require_once 'mededelingencontent.class.php';

class MededelingTopDrieOverzichtContent extends TemplateView {

	public function __construct() {
		parent::__construct(null, 'Top 3 mededelingenoverzicht');
		$this->smarty->assign('mededelingen_root', MededelingenContent::mededelingenRoot);
	}

	public function view() {
		$this->smarty->display('mededelingen/top3overzicht.tpl');
	}

}
