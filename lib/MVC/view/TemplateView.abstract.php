<?php

require_once 'MVC/view/View.interface.php';
require_once('smarty/libs/Smarty.class.php');

/**
 * TemplateView.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * The template engine compiles the templates
 * and displays them to the user.
 * 
 */
abstract class TemplateView extends Smarty implements View {

	protected $model;

	public function __construct(PersistenceModel $model) {
		parent::__construct();
		$this->model = $model;

		$this->setTemplateDir(SMARTY_TEMPLATE_DIR);
		$this->setCompileDir(SMARTY_COMPILE_DIR);
		//$this->setConfigDir(SMARTY_CONFIG_DIR); 
		$this->setCacheDir(SMARTY_CACHE_DIR);
		$this->caching = false;

		// frequently used things
		$this->assignByRef('this', $this);
		$this->assign('GLOBALS', $GLOBALS);
		$this->assign('CSR_PICS', CSR_PICS);
		$this->assign('loginlid', LoginLid::instance());
	}

	function getTitel() {
		return '';
	}

	public function getMelding() {
		return SimpleHTML::getMelding();
	}

}

?>