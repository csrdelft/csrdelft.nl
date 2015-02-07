<?php

/**
 * VerticalenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor verticalen.
 */
class VerticalenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, VerticalenModel::instance());
	}

}
