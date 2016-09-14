<?php

require_once 'model/betalen/FacturenModel.class.php';

/**
 * BetalenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BetalenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'facturen' => 'P_FORUM_READ'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'facturen';
		}
		parent::performAction($this->getParams(3));
	}

	public function GET_facturen() {
		$facturen = FacturenModel::instance()->find();
		$this->view = new JsonResponse($facturen);
	}

}
