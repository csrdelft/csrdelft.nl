<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
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

/**
 * LidInstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidInstellingenController extends AbstractController
{
	public function __construct(
		private readonly LidInstellingenRepository $lidInstellingenRepository
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/instellingen', methods: ['GET'])]
	public function beheer()
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
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/instellingen/update/{module}/{instelling}/{waarde}',
			methods: ['POST'],
			defaults: ['waarde' => null]
		)
	]
	public function update(Request $request, $module, $instelling, $waarde = null)
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
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/instellingen/opslaan', methods: ['POST'])]
	public function opslaan()
	{
		$this->lidInstellingenRepository->saveAll(); // fetches $_POST values itself
		$this->addFlash(FlashType::SUCCESS, 'Instellingen opgeslagen');
		return $this->redirectToRoute('csrdelft_lidinstellingen_beheer');
	}

	/**
	 * @param string $module
	 * @param string $key
	 * @return JsonResponse
	 * @Auth(P_ADMIN)
	 */
	#[Route(path: '/instellingen/reset/{module}/{key}', methods: ['POST'])]
	public function reset($module, $key)
	{
		$this->lidInstellingenRepository->resetForAll($module, $key);
		$this->addFlash(
			FlashType::SUCCESS,
			'Voor iedereen de instelling ge-reset naar de standaard waarde'
		);
		return new JsonResponse(true);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/instellingen/reset/mijn', methods: ['POST'])]
	public function resetUser()
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
