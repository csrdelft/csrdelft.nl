<?php

require_once 'view/groepen/WoonoordenView.class.php';

/**
 * WoonoordenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor woonoorden en huizen.
 */
class WoonoordenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, WoonoordenModel::instance());
	}

}
