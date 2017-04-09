<?php

require_once 'model/fiscaat/CiviSaldoModel.class.php';
require_once 'view/fiscaat/BeheerCiviSaldoView.class.php';

/**
 * BeheerCiviSaldoController.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class BeheerCiviSaldoController extends AclController {
	public function __construct($query) {
		parent::__construct($query, CiviSaldoModel::instance());

		if ($this->getMethod() == "POST") {
			$this->acl = [
				'overzicht' => 'P_MAAL_MOD',
				'registreren' => 'P_MAAL_MOD',
				'verwijderen' => 'P_MAAL_MOD'
			];
		} else {
			$this->acl = [
				'overzicht' => 'P_MAAL_MOD',
			];
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';

		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		return parent::performAction($this->getParams(4));
	}

	public function GET_overzicht() {
		$this->view = new CsrLayoutPage(new BeheerCiviSaldoView());
	}

	public function POST_overzicht() {
		$this->view = new BeheerSaldoResponse($this->model->find());
	}

	public function POST_verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		$civisaldo = $this->model->retrieveByUUID($selection[0]);

		if ($civisaldo) {
			$this->model->delete($civisaldo);
			$this->view = new RemoveRowsResponse(array($civisaldo));
			return;
		}

		$this->exit_http(404);
	}

	public function POST_registreren() {
		$form = new LidRegistratieForm(new CiviSaldo());

		if ($form->validate()) {
			$saldo = $form->getModel();
			$saldo->laatst_veranderd = date_create()->format(DATE_ISO8601);
			$this->model->create($saldo);
			$this->view = new BeheerSaldoResponse(array($saldo));
			return;
		}

		$this->view = $form;
	}
}