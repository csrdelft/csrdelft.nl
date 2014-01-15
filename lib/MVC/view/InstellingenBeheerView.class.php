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

	/**
	 * List of all modules
	 * @var array
	 */
	private $modules;
	/**
	 * List of instellingen of a module
	 * @var array
	 */
	private $instellingen;
	/**
	 * Instelling to update in view
	 * @var Instelling
	 */
	private $instelling;
	/**
	 * Currently viewed module
	 * @var string
	 */
	private $module;

	public function __construct(Instellingen $model, $module, Instelling $instelling = null) {
		parent::__construct($model);
		$this->module = $module;
		$this->instelling = $instelling;
		if ($instelling === null) {
			$this->modules = $model->getAlleModules();
			if ($module !== '') {
				$this->instellingen = $model->getModuleInstellingen($module);
			}
		}
	}

	public function getTitel() {
		if ($this->module !== '') {
			return 'Beheer instellingen module: ' . $this->module;
		}
		return 'Beheer instellingen stek';
	}

	public function view() {
		if ($this->instelling === null) {
			$this->smarty->assign('melding', $this->getMelding());
			$this->smarty->assign('kop', $this->getTitel());
			$this->smarty->assign('module', $this->module);
			$this->smarty->assign('modules', $this->modules);
			$this->smarty->assign('instellingen', $this->instellingen);
			$this->smarty->display('MVC/instellingen/beheer/instellingen_page.tpl');
		} else {
			$this->smarty->assign('instelling', $this->instelling);
			$this->smarty->display('MVC/instellingen/beheer/instelling_row.tpl');
		}
	}

}
