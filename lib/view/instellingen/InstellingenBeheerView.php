<?php

namespace CsrDelft\view\instellingen;

use CsrDelft\model\instellingen\InstellingenModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * InstellingenBeheerView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle instellingen om te beheren.
 * @property-read InstellingenModel $model
 */
class InstellingenBeheerView extends SmartyTemplateView {

	private $module;

	public function __construct(InstellingenModel $instellingen, $module) {
		parent::__construct($instellingen, 'Instellingenbeheer');
		$this->module = $module;
	}

	public function view() {
		if ($this->module !== null) {
			$this->titel = 'Beheer instellingen module: ' . $this->module;
			$this->smarty->assign('instellingen', $this->model->getModuleKeys($this->module));
		} else {
			$this->titel = 'Beheer instellingen stek';
		}
		$this->smarty->assign('module', $this->module);
		$this->smarty->assign('modules', $this->model->getModules());
		$this->smarty->assign('titel', 'Instellingenbeheer');
		$this->smarty->display('instellingen/beheer/instellingen_page.tpl');
	}

}
