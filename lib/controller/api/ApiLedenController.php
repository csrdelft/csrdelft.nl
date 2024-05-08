<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\LidZoekerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiLedenController
{
	/**
	 * @var LidZoekerService
	 */
	private $lidZoekerService;

	public function __construct(LidZoekerService $lidZoekerService)
	{
		$this->lidZoekerService = $lidZoekerService;
	}

	/**
	 * @Route("/API/2.0/leden", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function getLeden(): JsonResponse
	{
		$leden = [];

		foreach ($this->lidZoekerService->getLeden() as $profiel) {
			$leden[] = [
				'id' => $profiel->uid,
				'voornaam' => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam' => $profiel->achternaam,
			];
		}

		return new JsonResponse(['data' => $leden]);
	}

	/**
	 * @Route("/API/2.0/leden/{id}", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function getLid($id): JsonResponse
	{
		$profiel = ProfielRepository::get($id);

		if (!$profiel) {
			throw new NotFoundHttpException(404);
		}

		$woonoord = $profiel->getWoonoord();
		$lid = [
			'id' => $profiel->uid,
			'naam' => [
				'voornaam' => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam' => $profiel->achternaam,
				'formeel' => $profiel->getNaam('civitas'),
			],
			'pasfoto' => $profiel->getPasfotoPath('vierkant'),
			'geboortedatum' => DateUtil::dateFormatIntl(
				$profiel->gebdatum,
				DateUtil::DATE_FORMAT
			),
			'email' => $profiel->email,
			'mobiel' => $profiel->mobiel,
			'huis' => [
				'naam' => $woonoord ? $woonoord->naam : null,
				'adres' => $profiel->adres,
				'postcode' => $profiel->postcode,
				'woonplaats' => $profiel->woonplaats,
				'land' => $profiel->land,
			],
			'studie' => [
				'naam' => $profiel->studie,
				'sinds' => $profiel->studiejaar,
			],
			'lichting' => $profiel->lidjaar,
			'verticale' => !$profiel->getVerticale()
				? null
				: $profiel->getVerticale()->naam,
		];

		return new JsonResponse(['data' => $lid]);
	}
}
