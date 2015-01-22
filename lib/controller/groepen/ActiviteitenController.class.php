<?php

/**
 * ActiviteitenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor activiteiten.
 */
class ActiviteitenController extends KetzersController {

	public function __construct($query) {
		parent::__construct($query, ActiviteitenModel::instance());
	}

}
