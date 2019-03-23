<?php

namespace CsrDelft\controller;

use CsrDelft\controller\fiscaat\BeheerCiviBestellingController;
use CsrDelft\controller\fiscaat\BeheerCiviCategorienController;
use CsrDelft\controller\fiscaat\BeheerCiviProductenController;
use CsrDelft\controller\fiscaat\BeheerCiviSaldoController;
use CsrDelft\controller\fiscaat\PinTransactieController;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;

class FiscaatRouterController extends AclController {
	public function __construct($query) {
		parent::__construct($query, $query);

		$this->acl = array(
			'overzicht' => P_FISCAAT_READ,
			'producten' => P_FISCAAT_READ,
			'saldo' => P_FISCAAT_READ,
			'bestellingen' => P_MAAL_IK,
			'categorien' => P_FISCAAT_READ,
			'pin' => P_FISCAAT_READ,
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
		$this->view = view('fiscaat.overzicht', [
			'saldisomform' => new SaldiSomForm(CiviSaldoModel::instance()),
			'saldisom' => CiviSaldoModel::instance()->getSomSaldi(),
			'saldisomleden' => CiviSaldoModel::instance()->getSomSaldi(true),
			'productenbeheer' => new CiviProductTable(),
			'saldobeheer' => new CiviSaldoTable(),
		]);
	}

	public function producten() {
		return new BeheerCiviProductenController($this->model);
	}

	public function saldo() {
		return new BeheerCiviSaldoController($this->model);
	}

	public function bestellingen() {
		return new BeheerCiviBestellingController($this->model);
	}

	public function categorien() {
		return new BeheerCiviCategorienController($this->model);
	}

	public function pin() {
		return new PinTransactieController($this->model);
	}
}
