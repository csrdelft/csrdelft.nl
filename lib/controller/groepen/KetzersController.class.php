<?php

/**
 * KetzersController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor ketzers.
 */
class KetzersController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, KetzersModel::instance());
	}

}
