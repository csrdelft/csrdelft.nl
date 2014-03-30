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

	/**
	 * TODO: verplicht $model.
	 * 
	 * @param mixed $model
	 */
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
	 * @deprecated
	 * Backwards compatibility with SimpleHTML.
	 */
	public function getMelding() {
		return SimpleHTML::getMelding();
	}

	/**
	 * @deprecated
	 * Laad een lid object.
	 * 
	 * @return Lid if exists, false otherwise
	 */
	public function getLid($uid) {
		$lid = LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		return $lid;
	}

}
