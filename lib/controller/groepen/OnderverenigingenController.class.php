<?php

/**
 * OnderverenigingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor onderverenigingen.
 */
class OnderverenigingenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, OnderverenigingenModel::instance());
	}

}
