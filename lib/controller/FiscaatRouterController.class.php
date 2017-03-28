<?php

class FiscaatRouterController extends AclController {
	public function __construct($query) {
		parent::__construct($query, $query);

		$this->acl = array(
			'producten' => 'P_MAAL_MOD'
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$controller = parent::performAction();
		if ($controller !== null) {
			$controller->performAction();
			$this->view = $controller->getView();
		}
	}

	public function producten() {
		require_once 'controller/fiscaat/BeheerCiviProductenController.class.php';
		return new BeheerCiviProductenController($this->model);
	}
}