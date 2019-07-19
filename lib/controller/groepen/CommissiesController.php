<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\CommissiesModel;

/**
 * CommissiesController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissies.
 */
class CommissiesController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, CommissiesModel::instance());
	}

}
