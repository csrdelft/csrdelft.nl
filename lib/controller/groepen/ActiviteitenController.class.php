<?php

require_once 'view/groepen/ActiviteitenView.class.php';

/**
 * ActiviteitenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor activiteiten.
 */
class ActiviteitenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, ActiviteitenModel::instance());
	}

}
