<?php

require_once 'controller/groepen/KetzersController.class.php';

/**
 * WerkgroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor werkgroepen.
 */
class WerkgroepenController extends KetzersController {

	public function __construct($query) {
		parent::__construct($query, WerkgroepenModel::instance());
	}

}
