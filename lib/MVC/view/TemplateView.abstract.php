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

	/**
	 * @deprecated
	 * Backwards compatibility with SimpleHTML.
	 */
	public static function getMelding() {
		return SimpleHTML::getMelding();
	}

	/**
	 * @deprecated
	 * Laad een lid object.
	 * 
	 * @return Lid if exists, false otherwise
	 */
	public static function getLid($uid) {
		$lid = LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		return $lid;
	}

}
