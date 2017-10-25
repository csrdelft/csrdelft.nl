<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\OnderverenigingenModel;

/**
 * OnderverenigingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor onderverenigingen.
 */
class OnderverenigingenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, OnderverenigingenModel::instance());
	}

}
