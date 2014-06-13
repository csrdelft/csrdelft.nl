<?php

/**
 * SjaarciesController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor sjaarcies.
 */
class SjaarciesController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, SjaarciesModel::instance());
	}

}
