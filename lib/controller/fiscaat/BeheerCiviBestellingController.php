<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingInhoudTableResponse;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTable;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTableResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviBestellingController {
	/** @var CiviBestellingModel */
	private $civiBestellingModel;
	/** @var CiviBestellingInhoudModel  */
	private $civiBestellingInhoudModel;

	public function __construct(CiviBestellingModel $civiBestellingModel, CiviBestellingInhoudModel $civiBestellingInhoudModel) {
		$this->civiBestellingInhoudModel = $civiBestellingInhoudModel;
		$this->civiBestellingModel = $civiBestellingModel;
	}

	public function overzicht($uid = null) {
		$this->checkToegang($uid);

		return view('fiscaat.pagina', [
			'titel' => 'Beheer bestellingen',
			'view' => new CiviBestellingTable($uid)
		]);
	}

	public function lijst(Request $request, $uid = null) {
		$this->checkToegang($uid);
		$uid = $uid == null ? LoginService::getUid() : $uid;
		if ($request->query->get("deleted") == "true") {
			$data = $this->civiBestellingModel->find('uid = ?', array($uid));
		} else {
			$data = $this->civiBestellingModel->find('uid = ? and deleted = false', array($uid));
		}
		return new CiviBestellingTableResponse($data);
	}

	public function inhoud($bestelling_id) {
		$data = $this->civiBestellingInhoudModel->find('bestelling_id = ?', [$bestelling_id]);

		return new CiviBestellingInhoudTableResponse($data);
	}

	/**
	 * Alleen leden met P_FISCAAT_READ mogen het overzicht van andere leden zien.
	 *
	 * @param string $uid
	 */
	private function checkToegang($uid) {
		if (!LoginService::mag(P_FISCAAT_READ) && $uid) {
			throw new CsrToegangException();
		}
	}
}
