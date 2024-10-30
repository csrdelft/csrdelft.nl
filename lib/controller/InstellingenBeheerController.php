<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\instellingen\InstellingenRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * InstellingenBeheerController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class InstellingenBeheerController extends AbstractController
{
	public function __construct(
		private readonly InstellingenRepository $instellingenRepository
	) {
	}

	protected function assertToegang($module = null)
	{
		if (!$this->magModuleZien($module)) {
			throw $this->createAccessDeniedException();
		}
	}

	protected function magModuleZien($module = null)
	{
		if ($module) {
			return match ($module) {
				'agenda' => $this->mag(P_AGENDA_MOD),
				'corvee' => $this->mag(P_CORVEE_MOD),
				'maaltijden' => $this->mag(P_MAAL_MOD),
				default => $this->mag(P_ADMIN),
			};
		}
		return true; // hoofdpagina: geen module
	}

	/**
	 * @param null $module
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/instellingenbeheer/module/{module}', methods: ['GET'])]
	#[Route(path: '/instellingenbeheer', methods: ['GET'])]
	public function module($module = null)
	{
		$this->assertToegang($module);

		if (in_array($module, $this->instellingenRepository->getModules())) {
			$instellingen = array_map(
				fn($instelling) => $this->instellingenRepository->getInstelling(
					$module,
					$instelling
				),
				$this->instellingenRepository->getModuleKeys($module)
			);
		} else {
			$instellingen = null;
			$module = null;
		}

		return $this->render('instellingenbeheer/beheer.html.twig', [
			'module' => $module,
			'modules' => $this->instellingenRepository->getModules(),
			'instellingen' => $instellingen,
		]);
	}

	/**
	 * @param $module
	 * @param $id
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/instellingenbeheer/opslaan/{module}/{id}', methods: ['POST'])]
	public function opslaan($module, $id)
	{
		$this->assertToegang($module);

		$waarde = filter_input(INPUT_POST, 'waarde', FILTER_UNSAFE_RAW);
		$instelling = $this->instellingenRepository->wijzigInstelling(
			$module,
			$id,
			$waarde
		);

		return $this->render('instellingenbeheer/regel.html.twig', [
			'instelling' => $instelling,
		]);
	}

	/**
	 * @param $module
	 * @param $id
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/instellingenbeheer/reset/{module}/{id}', methods: ['POST'])]
	public function reset($module, $id)
	{
		$this->assertToegang($module);

		$instelling = $this->instellingenRepository->wijzigInstelling(
			$module,
			$id,
			$this->instellingenRepository->getDefault($module, $id)
		);

		return $this->render('instellingenbeheer/regel.html.twig', [
			'instelling' => $instelling,
		]);
	}
}
