<?php

class FiscaatRouterController extends AclController {
	public function __construct($query) {
		parent::__construct($query, $query);

		$this->acl = array(
			'overzicht' => 'P_MAAL_MOD',
			'producten' => 'P_MAAL_MOD',
			'saldo' => 'P_MAAL_MOD',
			'bestellingen' => 'P_MAAL_IK',
			'categorien' => 'P_MAAL_MOD'
		);
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$controller = parent::performAction($args);
		if ($controller !== null) {
			$controller->performAction();
			$this->view = $controller->getView();
		}
	}

	public function overzicht() {
		require_once 'view/fiscaat/FiscaatOverzichtView.class.php';
		$this->view = new CsrLayoutPage(new FiscaatOverzichtView(null));
	}

	public function producten() {
		require_once 'controller/fiscaat/BeheerCiviProductenController.class.php';
		return new BeheerCiviProductenController($this->model);
	}

	public function saldo() {
		require_once 'controller/fiscaat/BeheerCiviSaldoController.class.php';
		return new BeheerCiviSaldoController($this->model);
	}

	public function bestellingen() {
		require_once 'controller/fiscaat/BeheerCiviBestellingController.class.php';
		return new BeheerCiviBestellingController($this->model);
	}

	public function categorien() {
		require_once 'controller/fiscaat/BeheerCiviCategorienController.class.php';
		return new BeheerCiviCategorienController($this->model);
	}
}
