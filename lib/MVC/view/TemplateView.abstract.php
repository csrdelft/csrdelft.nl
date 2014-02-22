<?php

require_once 'MVC/view/View.interface.php';
require_once('MVC/view/CsrSmarty.class.php');

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
	 * @var mixed
	 */
	protected $model;
	/**
	 * Template engine
	 * @var CsrSmarty
	 */
	protected $smarty;

	public function __construct($model = null) {
		$this->model = $model;
		$this->smarty = new CsrSmarty();
		$this->smarty->assignByRef('view', $this);
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return '';
	}

	/**
	 * Backwards compatibility with SimpleHTML
	 * @deprecated
	 */
	public function getMelding() {
		return SimpleHTML::getMelding();
	}

}
