<?php

/**
 * KringenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor kringen.
 */
class KringenController extends OpvolgbareGroepenController {

	public function __construct($query) {
		parent::__construct($query, KringenModel::instance());
	}

}
