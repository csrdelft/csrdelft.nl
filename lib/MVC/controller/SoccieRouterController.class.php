<?php

/**
 * SoccieRouterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Router voor de soccie module.
 */
class SoccieRouterController extends AclController {

	public function __construct($query) {
		$query = str_replace('soccie/', 'soccie', $query);
		parent::__construct($query, $query); // Use model to pass through query
		$this->acl = array(
			'socciesaldo' => 'P_LOGGED_IN'
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		if ($this->action === 'soccie') {
			$this->action = 'socciesaldo';
		}
		$controller = parent::performAction();
		if ($controller !== null) {
			$controller->performAction();
			$this->view = $controller->getView();
		}
	}

	public function socciesaldo() {
		require_once 'MVC/controller/soccie/SocCieKlantenController.class.php';
		return new SocCieKlantenController($this->model);
	}

}
