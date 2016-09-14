<?php

require_once 'model/betalen/FacturenModel.class.php';
require_once 'view/BetalenView.class.php';

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
				'application'	 => 'P_BETALEN_IK',
				'facturen'		 => 'P_BETALEN_IK'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'application';
		}
		parent::performAction($this->getParams(3));
	}

	public function GET_application() {
		$body = new BetalenView(null);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('betalen');
	}

	public function GET_facturen() {
		$facturen = FacturenModel::instance()->find();
		$this->view = new JsonLijstResponse($facturen);
	}

}
