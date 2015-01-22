<?php

/**
 * WerkgroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor werkgroepen.
 */
class WerkgroepenController extends OpvolgbareGroepenController {

	public function __construct($query) {
		parent::__construct($query, WerkgroepenModel::instance());
	}

}
