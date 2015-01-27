<?php

/**
 * KetzersController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor ketzers.
 */
class KetzersController extends GroepenController {

	public function __construct($query, KetzersModel $model = null) {
		parent::__construct($query, $model);
		if ($model === null) {
			$this->model = KetzersModel::instance();
		}
	}

}
