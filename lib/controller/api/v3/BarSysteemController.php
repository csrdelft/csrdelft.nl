<?php

namespace CsrDelft\controller\api\v3;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\bar\BarLocatie;
use CsrDelft\service\BarSysteemService;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Class BarSysteemController
 * @package CsrDelft\controller\api\v3
 */
#[Route(path: '/api/v3/bar')]
class BarSysteemController extends AbstractController
{
	public function __construct(
		private readonly BarSysteemService $barSysteemService
	) {
	}

	protected function json(
		$data,
		int $status = 200,
		array $headers = [],
		array $context = []
	): JsonResponse {
		return parent::json(
			$data,
			$status,
			$headers,
			$context + ['groups' => ['bar']]
		);
	}

	/**
	 * @Auth(P_FISCAAT_MOD)
	 * @IsGranted("ROLE_OAUTH2_BAR:TRUST")
	 * @param Request $request
	 * @return JsonResponse
	 */
	#[Route(path: '/trust', methods: ['POST'])]
	public function trust(Request $request)
	{
		// maak een nieuwe BarSysteemTrust object en sla op.

		// Als het goed is kan de BAR:TRUST scope alleen aan mensen met FISCAAT_MOD rechten gegeven worden.
		$this->denyAccessUnlessGranted(
			'ROLE_FISCAAT_MOD',
			null,
			'Moet fiscus zijn.'
		);

		$barLocatie = new BarLocatie();
		$barLocatie->ip = $request->getClientIp();
		$barLocatie->naam = $request->request->get('naam');
		$barLocatie->sleutel = Uuid::v4();
		$barLocatie->doorAccount = $this->getUser();

		$objectManager = $this->getDoctrine()->getManager();

		$objectManager->persist($barLocatie);
		$objectManager->flush();

		return $this->json($barLocatie, 200, [], ['groups' => ['json']]);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
	 */
	#[Route(path: '/updatePerson', methods: ['POST'])]
	public function updatePerson(Request $request)
	{
		$id = $request->request->get('id');
		$name = $request->request->get('name');
		$this->barSysteemService->updatePerson($id, $name);

		return new Response('', 204);
	}

	/**
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/personen', methods: ['POST'])]
	public function personen()
	{
		return $this->json($this->barSysteemService->getPersonen());
	}

	/**
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/producten', methods: ['POST'])]
	public function producten()
	{
		return $this->json($this->barSysteemService->getProducten());
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @throws \Doctrine\DBAL\ConnectionException
	 * @throws \Doctrine\DBAL\Driver\Exception
	 * @throws \Doctrine\DBAL\Exception
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/bestelling', methods: ['POST'])]
	public function bestelling(Request $request)
	{
		$uid = $request->request->get('uid');
		$inhoud = $request->request->get('inhoud');

		if ($request->request->has('oudeBestelling')) {
			$bestelId = $request->request->get('oudeBestelling');
			$this->barSysteemService->log('update', $_POST);

			$this->barSysteemService->updateBestelling($uid, $bestelId, $inhoud);

			return new Response('', 204);
		} else {
			$this->barSysteemService->log('insert', $_POST);

			$this->barSysteemService->verwerkBestelling($uid, 'soccie', $inhoud);

			return new Response('', 204);
		}
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/saldo', methods: ['POST'])]
	public function saldo(Request $request)
	{
		$soccieSaldoId = $request->request->get('saldoSocCieId');
		return $this->json($this->barSysteemService->getSaldo($soccieSaldoId));
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/verwijderBestelling', methods: ['POST'])]
	public function verwijderBestelling(Request $request)
	{
		$this->barSysteemService->log('remove', $_POST);

		$bestelling = $request->request->get('verwijderBestelling');

		$this->barSysteemService->verwijderBestelling($bestelling);

		return new Response('', 204);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/undoVerwijderBestelling', methods: ['POST'])]
	public function undoVerwijderBestelling(Request $request)
	{
		$this->barSysteemService->log('remove', $_POST);
		$data = $request->request->get('undoVerwijderBestelling');

		$this->barSysteemService->undoVerwijderBestelling($data);

		return new Response('', 204);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	#[Route(path: '/laadLaatste', methods: ['POST'])]
	public function laadLaatste(Request $request)
	{
		$persoon = $request->request->get('aantal');
		$begin = date_create_immutable($request->request->get('begin'));
		$eind = date_create_immutable($request->request->get('eind'));

		if (!$begin || !$eind) {
			throw new BadRequestHttpException(
				'Begin en eind moeten een datum bevatten'
			);
		}

		$productType = $request->request->get('productType', []);
		return $this->json(
			$this->barSysteemService->getBestellingLaatste(
				$persoon,
				$begin,
				$eind,
				$productType
			)
		);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/prakciePilsjes')]
	public function prakciePilsjes(Request $request)
	{
		$vanaf = date_create_immutable($request->query->get('vanaf', 'now'));
		if ($vanaf === false) {
			return new Response('Verkeerde formaat voor datum', 400);
		}
		$pilsjes = $this->barSysteemService->getPrakCiePilsjes($vanaf);
		$res = new Response((string) $pilsjes, 200);
		$res->headers->set('Content-Type', 'text/plain');
		return $res;
	}
}
