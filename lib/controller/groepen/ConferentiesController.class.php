<?php

/**
 * ConferentiesController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor conferenties.
 */
class ConferentiesController extends ActiviteitenController {

	public function __construct($query) {
		parent::__construct($query, ConferentiesModel::instance());
	}

}
