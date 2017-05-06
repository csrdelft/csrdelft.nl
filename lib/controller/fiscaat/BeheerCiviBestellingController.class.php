<?php

require_once 'model/fiscaat/CiviBestellingModel.class.php';
require_once 'view/fiscaat/CiviBestellingOverzichtView.class.php';
require_once 'view/fiscaat/CiviBestellingOverzichtResponse.class.php';

/**
 * Class BeheerCiviBestellingController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @property CiviBestellingModel $model
 */
class BeheerCiviBestellingController extends AclController {
	public function __construct($query) {
		parent::__construct($query, CiviBestellingModel::instance());

		if ($this->getMethod() == "POST") {
			$this->acl = [
				'mijn' => 'P_MAAL_IK',
				'overzicht' => 'P_MAAL_MOD'
			];
		} else {
			$this->acl = [
				'mijn' => 'P_MAAL_IK',
				'overzicht' => 'P_MAAL_MOD'
			];
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'mijn';

		if ($this->hasParam(3)) {
			if ($this->getParam(3) != LoginModel::getUid()) {
				$this->action = 'overzicht';
			}
		}

		return parent::performAction($this->getParams(3));
	}

	public function GET_overzicht($uid) {
		$this->view = new CsrLayoutPage(new CiviBestellingOverzichtView($uid));
	}

	public function POST_overzicht($uid) {
		$this->view = new CiviBestellingOverzichtResponse($this->model->find('uid = ?', array($uid)));
	}

	public function GET_mijn() {
		$this->GET_overzicht(LoginModel::getUid());
	}

	public function POST_mijn() {
		$this->POST_overzicht(LoginModel::getUid());
	}
}
