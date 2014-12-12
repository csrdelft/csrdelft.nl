<?php

require_once 'view/groepen/CommissiesView.class.php';

/**
 * CommissiesController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor commissies.
 */
class CommissiesController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, CommissiesModel::instance());
	}

}
