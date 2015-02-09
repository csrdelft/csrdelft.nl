<?php

/**
 * BesturenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor besturen.
 */
class BesturenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, BesturenModel::instance());
	}

}
