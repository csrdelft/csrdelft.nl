<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\ActiviteitenModel;


/**
 * ApiActiviteitenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor activiteiten.
 */
class ActiviteitenController extends KetzersController {
	public function __construct() {
		parent::__construct();
		$this->model = ActiviteitenModel::instance();
	}
}
