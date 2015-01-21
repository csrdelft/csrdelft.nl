<?php

/**
 * KringenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor kringen.
 */
class KringenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, KringenModel::instance());
	}

}
