<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTable;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviBestellingController extends AbstractController {
	/** @var CiviBestellingRepository */
	private $civiBestellingRepository;
	/** @var CiviBestellingInhoudRepository  */
	private $civiBestellingInhoudRepository;

	public function __construct(CiviBestellingRepository $civiBestellingRepository, CiviBestellingInhoudRepository $civiBestellingInhoudRepository) {
		$this->civiBestellingInhoudRepository = $civiBestellingInhoudRepository;
		$this->civiBestellingRepository = $civiBestellingRepository;
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
			$data = $this->civiBestellingRepository->findBy(['uid' => $uid]);
		} else {
			$data = $this->civiBestellingRepository->findBy(['uid' => $uid, 'deleted' => false]);
		}
		return $this->tableData($data);
	}

	public function inhoud($bestelling_id) {
		$data = $this->civiBestellingInhoudRepository->findBy(['bestelling_id' => $bestelling_id]);

		return $this->tableData($data);
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
