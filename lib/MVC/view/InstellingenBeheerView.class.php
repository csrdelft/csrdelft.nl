<?php

/**
 * InstellingenBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle instellingen om te beheren.
 * 
 */
class InstellingenBeheerView extends SmartyTemplateView {

	private $module;

	public function __construct(Instellingen $instellingen, $module) {
		parent::__construct($instellingen, 'Instellingenbeheer');
		$this->module = $module;
	}

	public function view() {
		if ($this->module !== null) {
			$this->titel = 'Beheer instellingen module: ' . $this->module;
			$this->smarty->assign('instellingen', $this->model->getModuleInstellingen($this->module));
		} else {
			$this->titel = 'Beheer instellingen stek';
		}
		$this->smarty->assign('module', $this->module);
		$this->smarty->assign('modules', $this->model->getModules());
		$this->smarty->display('instellingen/beheer/instellingen_page.tpl');
	}

}

class InstellingBeheerView extends SmartyTemplateView {

	public function __construct(Instelling $instelling) {
		parent::__construct($instelling);
	}

	public function view() {
		$this->smarty->assign('module', $this->model->module);
		$this->smarty->assign('id', $this->model->instelling_id);
		$this->smarty->assign('waarde', $this->model->waarde);
		$this->smarty->display('instellingen/beheer/instelling_row.tpl');
	}

}
