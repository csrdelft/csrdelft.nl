<?php

/**
 * KetzersController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor ketzers.
 */
class KetzersController extends OpvolgbareGroepenController {

	public function __construct($query) {
		parent::__construct($query, KetzersModel::instance());
	}

}
