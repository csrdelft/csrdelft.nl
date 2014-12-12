<?php

require_once 'view/groepen/WerkgroepenView.class.php';

/**
 * WerkgroepenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor werkgroepen.
 */
class WerkgroepenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, WerkgroepenModel::instance());
	}

}
