<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviBestellingController extends AbstractController
{
	/** @var CiviBestellingRepository */
	private $civiBestellingRepository;
	/** @var CiviBestellingInhoudRepository  */
	private $civiBestellingInhoudRepository;

	public function __construct(
		CiviBestellingRepository $civiBestellingRepository,
		CiviBestellingInhoudRepository $civiBestellingInhoudRepository
	) {
		$this->civiBestellingInhoudRepository = $civiBestellingInhoudRepository;
		$this->civiBestellingRepository = $civiBestellingRepository;
	}

	/**
	 * @param null $uid
	 * @return Response
	 * @Route("/fiscaat/bestellingen/{uid}", methods={"GET"}, defaults={"uid"=null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function overzicht($uid = null)
	{
		$this->checkToegang($uid);

		return $this->render('fiscaat/pagina.html.twig', [
			'titel' => 'Beheer bestellingen',
			'view' => new CiviBestellingTable($uid),
		]);
	}

	/**
	 * @param Request $request
	 * @param null $uid
	 * @return GenericDataTableResponse
	 * @Route("/fiscaat/bestellingen/{uid}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijst(Request $request, $uid = null)
	{
		$this->checkToegang($uid);
		$uid = $uid == null ? $this->getUid() : $uid;
		if ($request->query->get('deleted') == 'true') {
			$data = $this->civiBestellingRepository->findBy(['uid' => $uid]);
		} else {
			$data = $this->civiBestellingRepository->findBy([
				'uid' => $uid,
				'deleted' => false,
			]);
		}
		return $this->tableData($data);
	}

	/**
	 * @param $bestelling_id
	 * @return GenericDataTableResponse
	 * @Route("/fiscaat/bestellingen/inhoud/{bestelling_id}", methods={"POST"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function inhoud($bestelling_id)
	{
		$data = $this->civiBestellingInhoudRepository->findBy([
			'bestelling_id' => $bestelling_id,
		]);

		return $this->tableData($data);
	}

	/**
	 * Alleen leden met P_FISCAAT_READ mogen het overzicht van andere leden zien.
	 *
	 * @param string $uid
	 */
	private function checkToegang($uid)
	{
		if (!$this->mag(P_FISCAAT_READ) && $uid) {
			throw $this->createAccessDeniedException();
		}
	}
}
