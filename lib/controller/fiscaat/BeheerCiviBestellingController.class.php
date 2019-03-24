<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingInhoudTableResponse;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTable;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTableResponse;

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
				'mijn' => P_MAAL_IK,
				'overzicht' => P_FISCAAT_READ,
				'inhoud' => P_FISCAAT_READ,
			];
		} else {
			$this->acl = [
				'mijn' => P_MAAL_IK,
				'overzicht' => P_FISCAAT_READ
			];
		}
	}

	/**
	 * @param array $args
	 * @return mixed
	 * @throws CsrException
	 */
	public function performAction(array $args = array()) {
		$this->action = 'mijn';

		if ($this->hasParam(4)) {
			$this->action = $this->getParam(3);

			return parent::performAction($this->getParams(4));
		}

		if ($this->hasParam(3)) {
			$this->action = 'overzicht';
		}

		return parent::performAction($this->getParams(3));
	}

	public function GET_overzicht($uid = null) {
		$this->view = view('fiscaat.pagina', [
			'titel' => 'Beheer bestellingen',
			'view' => new CiviBestellingTable($uid)
		]);
	}

	public function POST_overzicht($uid) {
		if ($this->hasParam("deleted") && $this->getParam("deleted") == "true") {
			$data = $this->model->find('uid = ?', array($uid));
		} else {
			$data = $this->model->find('uid = ? and deleted = false', array($uid));
		}
		$this->view = new CiviBestellingTableResponse($data);
	}

	public function GET_mijn() {
		$this->GET_overzicht();
	}

	public function POST_mijn() {
		$this->POST_overzicht(LoginModel::getUid());
	}

	public function POST_inhoud($bestelling_id) {
		$data = CiviBestellingInhoudModel::instance()->find('bestelling_id = ?', [$bestelling_id]);

		$this->view = new CiviBestellingInhoudTableResponse($data);
	}
}
