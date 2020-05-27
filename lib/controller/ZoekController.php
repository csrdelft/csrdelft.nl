<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\groepen\AbstractGroepenController;
use CsrDelft\controller\groepen\CommissiesController;
use CsrDelft\controller\groepen\KringenController;
use CsrDelft\controller\groepen\OnderverenigingenController;
use CsrDelft\controller\groepen\RechtengroepenController;
use CsrDelft\controller\groepen\WerkgroepenController;
use CsrDelft\controller\groepen\WoonoordenController;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ZoekController {
	/**
	 * @param Request $request
	 * @param DocumentenController $documentenController
	 * @param AgendaController $agendaController
	 * @param ToolsController $toolsController
	 * @param ForumController $forumController
	 * @param FotoAlbumController $fotoAlbumController
	 * @param BibliotheekController $bibliotheekController
	 * @param CommissiesController $commissiesController
	 * @param KringenController $kringenController
	 * @param OnderverenigingenController $onderverenigingenController
	 * @param WerkgroepenController $werkgroepenController
	 * @param WoonoordenController $woonoordenController
	 * @param RechtengroepenController $rechtengroepenController
	 * @return JsonResponse
	 * @Route("/zoeken", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function zoeken(
		Request $request,
		DocumentenController $documentenController,
		AgendaController $agendaController,
		ToolsController $toolsController,
		ForumController $forumController,
		FotoAlbumController $fotoAlbumController,
		BibliotheekController $bibliotheekController,
		CommissiesController $commissiesController,
		KringenController $kringenController,
		OnderverenigingenController $onderverenigingenController,
		WerkgroepenController $werkgroepenController,
		WoonoordenController $woonoordenController,
		RechtengroepenController $rechtengroepenController
	) {
		$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
		$resultaat = [];

		$instelling = lid_instelling('zoeken', 'leden');
		if ($instelling !== 'nee') {
			$resultaat['Leden'] = $toolsController->naamsuggesties('leden', $zoekterm)->getModel();
		}

		/** @var AbstractGroepenController[] $groepen */
		$groepen = [
			'commissies' => $commissiesController,
			'kringen' => $kringenController,
			'onderverenigingen' => $onderverenigingenController,
			'werkgroepen' => $werkgroepenController,
			'woonoorden' => $woonoordenController,
			'groepen' => $rechtengroepenController,
		];
		foreach ($groepen as $option => $controller) {
			if (lid_instelling('zoeken', $option) === 'ja') {
				$resultaat[ucfirst($option)] = $controller->zoeken($request, $zoekterm)->getModel();
			}
		}

		if (lid_instelling('zoeken', 'forum') === 'ja') {
			$resultaat['Forum'] = $forumController->titelzoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'fotoalbum') === 'ja') {
			$resultaat['Fotoalbum'] = $fotoAlbumController->zoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'agenda') === 'ja') {
			$resultaat['Agenda'] = $agendaController->zoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'documenten') === 'ja') {
			$resultaat['Documenten'] = $documentenController->zoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'boeken') === 'ja') {
			$resultaat['Boeken'] = $bibliotheekController->zoeken($request, $zoekterm)->getModel();
		}

		return new JsonResponse(array_merge(...array_values($resultaat)));
	}
}
