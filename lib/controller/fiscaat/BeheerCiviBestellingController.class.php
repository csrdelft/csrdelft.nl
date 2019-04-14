<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingInhoudTableResponse;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTable;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviBestellingController {
	use QueryParamTrait;
	/** @var CiviBestellingModel */
	private $model;

	public function __construct() {
		$this->model = CiviBestellingModel::instance();
	}

	public function overzicht($uid = null) {
		return view('fiscaat.pagina', [
			'titel' => 'Beheer bestellingen',
			'view' => new CiviBestellingTable($uid)
		]);
	}

	public function lijst($uid = null) {
		$uid = $uid == null ? LoginModel::getUid() : $uid;
		if ($this->hasParam("deleted") && $this->getParam("deleted") == "true") {
			$data = $this->model->find('uid = ?', array($uid));
		} else {
			$data = $this->model->find('uid = ? and deleted = false', array($uid));
		}
		return new CiviBestellingTableResponse($data);
	}

	public function inhoud($bestelling_id) {
		$data = CiviBestellingInhoudModel::instance()->find('bestelling_id = ?', [$bestelling_id]);

		return new CiviBestellingInhoudTableResponse($data);
	}
}
