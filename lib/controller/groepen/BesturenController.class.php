<?php

/**
 * BesturenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor besturen.
 */
class BesturenController extends CommissiesController {

	public function __construct($query) {
		parent::__construct($query, BesturenModel::instance());
	}

}
