<?php

/**
 * CommissiesController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor commissies.
 */
class CommissiesController extends OpvolgbareGroepenController {

	public function __construct($query) {
		parent::__construct($query, CommissiesModel::instance());
	}

}
