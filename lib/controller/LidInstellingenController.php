<?php

namespace CsrDelft\controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\view\login\OAuth2RefreshTokenTable;
use CsrDelft\view\login\OAuth2RememberTable;
use CsrDelft\view\login\RememberLoginTable;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LidInstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidInstellingenController extends AbstractController
{
	/** @var LidInstellingenRepository  */
	private $lidInstellingenRepository;

	public function __construct(
		LidInstellingenRepository $lidInstellingenRepository
	) {
		$this->lidInstellingenRepository = $lidInstellingenRepository;
	}

	/**
	 * @return Response
	 * @Route("/instellingen", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheer(): Response
	{
		return $this->render('instellingen/lidinstellingen.html.twig', [
			'defaultInstellingen' => $this->lidInstellingenRepository->getAll(),
			'instellingen' => $this->lidInstellingenRepository->getAllForLid(
				$this->getUid()
			),
			'rememberLoginTable' => new RememberLoginTable(),
			'authorizationCodeTable' => new OAuth2RefreshTokenTable(),
			'rememberOauthTable' => $this->createDataTable(
				OAuth2RememberTable::class
			)->createView(),
		]);
	}

	/**
	 * @param Request $request
	 * @param $module
	 * @param $instelling
	 * @param null $waarde
	 * @return JsonResponse
	 * @Route("/instellingen/update/{module}/{instelling}/{waarde}", methods={"POST"}, defaults={"waarde": null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function update(Request $request, $module, $instelling, $waarde = null): JsonResponse
	{
		if ($waarde === null) {
			$waarde = $request->request->get('waarde');
		}

		if (
			$this->lidInstellingenRepository->isValidValue(
				$module,
				$instelling,
				urldecode($waarde)
			)
		) {
			$this->lidInstellingenRepository->wijzigInstelling(
				$module,
				$instelling,
				urldecode($waarde)
			);
			return new JsonResponse(['success' => true]);
		} else {
			return new JsonResponse(['success' => false], 400);
		}
	}

	/**
	 * @throws Exception
	 * @Route("/instellingen/opslaan", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function opslaan(): RedirectResponse
	{
		$this->lidInstellingenRepository->saveAll(); // fetches $_POST values itself
		$this->addFlash(FlashType::SUCCESS, 'Instellingen opgeslagen');
		return $this->redirectToRoute('csrdelft_lidinstellingen_beheer');
	}

	/**
	 * @param string $module
	 * @param string $key
	 * @return JsonResponse
	 * @Route("/instellingen/reset/{module}/{key}", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function reset($module, $key): JsonResponse
	{
		$this->lidInstellingenRepository->resetForAll($module, $key);
		$this->addFlash(
			FlashType::SUCCESS,
			'Voor iedereen de instelling ge-reset naar de standaard waarde'
		);
		return new JsonResponse(true);
	}

	/**
	 * @Route("/instellingen/reset/mijn", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function resetUser(): Response
	{
		$account = $this->getUser();

		if (!$account) {
			$this->addFlash(FlashType::ERROR, 'Geen account');
			return new Response(
				$this->redirectToRoute(
					'csrdelft_lidinstellingen_beheer'
				)->getTargetUrl()
			);
		}

		$this->lidInstellingenRepository->resetForUser($account->profiel);

		$this->addFlash(FlashType::SUCCESS, 'Instellingen terug gezet');
		return new Response(
			$this->redirectToRoute('csrdelft_lidinstellingen_beheer')->getTargetUrl()
		);
	}
}
