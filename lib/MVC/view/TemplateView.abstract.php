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
	 * Titel
	 * @var string
	 */
	protected $titel;
	/**
	 * Template engine
	 * @var CsrSmarty
	 */
	protected $smarty;

	/**
	 * TODO: verplicht $model.
	 * 
	 * @param mixed $model
	 */
	public function __construct($model = null, $titel = '') {
		$this->model = $model;
		$this->titel = $titel;
		$this->smarty = new CsrSmarty();
		$this->smarty->assignByRef('view', $this);
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return $this->titel;
	}

}
