<?php

/**
 * InstellingenBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle instellingen om te beheren.
 * 
 */
class InstellingenBeheerView extends TemplateView {

	public function __construct(Instellingen $instellingen, $module) {
		parent::__construct($instellingen);
		if ($module !== null) {
			$this->titel = 'Beheer instellingen module: ' . $module;
			$this->smarty->assign('instellingen', $this->model->getModuleInstellingen($module));
		} else {
			$this->titel = 'Beheer instellingen stek';
			$this->smarty->assign('instellingen', array());
		}
		$this->smarty->assign('modules', $this->model->getAlleModules());
	}

	public function view() {
		$this->smarty->display('MVC/instellingen/beheer/instellingen_page.tpl');
	}

}

class InstellingBeheerView extends TemplateView {

	public function __construct(Instelling $instelling) {
		parent::__construct();
		$this->smarty->assign('instelling', $instelling);
	}

	public function view() {

		$this->smarty->display('MVC/instellingen/beheer/instelling_row.tpl');
	}

}
