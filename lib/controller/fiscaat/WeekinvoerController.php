<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeekinvoerController extends AbstractController {
	/**
	 * @Route("/fiscaat/weekinvoer")
	 * @param Request $request
	 * @param CiviSaldoRepository $civiSaldoRepository
	 * @return Response
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function weekinvoer(Request $request, CiviSaldoRepository $civiSaldoRepository) {
		$from = new DateTimeImmutable();
		$from = $from->sub(new DateInterval('P1M'));

		$until = new DateTimeImmutable();
		$until = $until->add(new DateInterval('P1W'));

		if ($request->request->has('van')) {
			try {
				$from = new DateTimeImmutable($request->request->get('van'));
			} catch (Exception $e) {
			}
		}

		if ($request->request->has('tot')) {
			try {
				$until = new DateTimeImmutable($request->request->get('tot'));
			} catch (Exception $e) {
			}
		}

		if ($from > $until) {
			$until = $from->add(new DateInterval('P1W'));
		}

		$weekinvoer = $civiSaldoRepository->getWeekinvoer($from, $until);

		return $this->render('fiscaat/weekinvoer.html.twig', [
			'van' => $from->format('Y-m-d'),
			'tot' => $until->format('Y-m-d'),
			'weekinvoeren' => $weekinvoer->weken,
			'categorieen' => $weekinvoer->categorieen
		]);
	}
}
