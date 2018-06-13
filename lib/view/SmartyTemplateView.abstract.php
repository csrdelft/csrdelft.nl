<?php

namespace CsrDelft\view;


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

	public function __construct($model, $titel = false) {
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

    public function __toString() {
        ob_start();
        $this->view();
        return ob_get_clean();
    }
}
