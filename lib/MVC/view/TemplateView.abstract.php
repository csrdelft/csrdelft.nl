<?php

require_once 'MVC/view/View.interface.php';
require_once('smarty/libs/Smarty.class.php');

/**
 * TemplateView.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses the template engine Smarty to compile and
 * display the template.
 * 
 */
abstract class TemplateView implements View {

	/**
	 * Data access model
	 */
	protected $model;
	/**
	 * Template engine
	 * @var Smarty
	 */
	protected $smarty;

	public function __construct($model = null) {
		$this->model = $model;
		$this->smarty = new Smarty();

		$this->smarty->setTemplateDir(SMARTY_TEMPLATE_DIR);
		$this->smarty->setCompileDir(SMARTY_COMPILE_DIR);
		//$this->smarty->setConfigDir(SMARTY_CONFIG_DIR); 
		$this->smarty->setCacheDir(SMARTY_CACHE_DIR);
		$this->smarty->caching = false;

		// frequently used things
		$this->smarty->assignByRef('view', $this);
		$this->smarty->assign('instellingen', Instellingen::instance());
		$this->smarty->assign('loginlid', LoginLid::instance());
		$this->smarty->assign('CSR_PICS', CSR_PICS);
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return '';
	}

	/**
	 * Backwards compatibility with SimpleHTML
	 */
	public function getMelding() {
		return SimpleHTML::getMelding();
	}

	/**
	 * Backwards compatibility with SimpleHTML
	 */
	public static function setMelding($sMelding, $level = -1) {
		setMelding($sMelding, $level);
	}

}
