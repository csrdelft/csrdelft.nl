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
use Symfony\Component\Routing\Attribute\Route;

class WeekinvoerController extends AbstractController
{
	/**
	 * @param Request $request
	 * @param CiviSaldoRepository $civiSaldoRepository
	 * @return Response
	 * @Auth(P_FISCAAT_READ)
	 */
	#[Route(path: '/fiscaat/weekinvoer')]
	public function weekinvoer(
		Request $request,
		CiviSaldoRepository $civiSaldoRepository
	) {
		$from = new DateTimeImmutable();
		$from = $from->sub(new DateInterval('P1M'));

		$until = new DateTimeImmutable();
		$until = $until->add(new DateInterval('P1W'));

		if ($request->query->has('van')) {
			try {
				$from = new DateTimeImmutable($request->query->get('van'));
			} catch (Exception) {
			}
		}

		if ($request->query->has('tot')) {
			try {
				$until = new DateTimeImmutable($request->query->get('tot'));
			} catch (Exception) {
			}
		}

		$weergave = 'tabel';
		if ($request->query->has('weergave')) {
			$weergave = $request->query->get('weergave');
		}

		if ($from > $until) {
			$until = $from->add(new DateInterval('P1W'));
		}

		$weekinvoer = $civiSaldoRepository->getWeekinvoer($from, $until);

		return $this->render('fiscaat/weekinvoer.html.twig', [
			'van' => $from->format('Y-m-d'),
			'tot' => $until->format('Y-m-d'),
			'weergave' => $weergave,
			'weekinvoeren' => $weekinvoer->weken,
			'categorieen' => $weekinvoer->categorieen,
		]);
	}
}
