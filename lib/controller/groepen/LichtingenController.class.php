<?php

/**
 * LichtingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor lichtingen.
 */
class LichtingenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, LichtingenModel::instance());
	}

}
