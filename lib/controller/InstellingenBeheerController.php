<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * InstellingenBeheerController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class InstellingenBeheerController extends AbstractController
{
	/**
	 * @var InstellingenRepository
	 */
	private $instellingenRepository;

	public function __construct(InstellingenRepository $instellingenRepository)
	{
		$this->instellingenRepository = $instellingenRepository;
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
			switch ($module) {
				case 'agenda':
					return LoginService::mag(P_AGENDA_MOD);
				case 'corvee':
					return LoginService::mag(P_CORVEE_MOD);
				case 'maaltijden':
					return LoginService::mag(P_MAAL_MOD);
				default:
					return LoginService::mag(P_ADMIN);
			}
		}
		return true; // hoofdpagina: geen module
	}

	/**
	 * @param null $module
	 * @return Response
	 * @Route("/instellingenbeheer/module/{module}", methods={"GET"})
	 * @Route("/instellingenbeheer", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function module($module = null)
	{
		$this->assertToegang($module);

		if (in_array($module, $this->instellingenRepository->getModules())) {
			$instellingen = array_map(function ($instelling) use ($module) {
				return $this->instellingenRepository->getInstelling($module, $instelling);
			}, $this->instellingenRepository->getModuleKeys($module));
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
	 * @Route("/instellingenbeheer/opslaan/{module}/{id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function opslaan($module, $id)
	{
		$this->assertToegang($module);

		$waarde = filter_input(INPUT_POST, 'waarde', FILTER_UNSAFE_RAW);
		$instelling = $this->instellingenRepository->wijzigInstelling($module, $id, $waarde);

		return $this->render('instellingenbeheer/regel.html.twig', ['instelling' => $instelling]);
	}

	/**
	 * @param $module
	 * @param $id
	 * @return Response
	 * @Route("/instellingenbeheer/reset/{module}/{id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function reset($module, $id)
	{
		$this->assertToegang($module);

		$instelling = $this->instellingenRepository->wijzigInstelling($module, $id, $this->instellingenRepository->getDefault($module, $id));

		return $this->render('instellingenbeheer/regel.html.twig', ['instelling' => $instelling]);
	}
}
