<?php

require_once 'view/CsrSmarty.singleton.php';

/**
 * SmartyTemplateView.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses the template engine Smarty to compile and
 * display the template.
 * 
 */
abstract class SmartyTemplateView implements View {

	/**
	 * Data model
	 * @var mixed
	 */
	protected $model;
	/**
	 * Titel
	 * @var string
	 */
	protected $titel;
	/**
	 * Template engine
	 * @var CsrSmarty
	 */
	protected $smarty;

	public function __construct($model, $titel = null) {
		$this->model = $model;
		$this->titel = $titel;
		$this->smarty = CsrSmarty::instance();
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getBreadcrumbs() {
		return null;
	}

	abstract function view();
}
