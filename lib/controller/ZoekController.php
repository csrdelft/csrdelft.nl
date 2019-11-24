<?php


namespace CsrDelft\controller;


use CsrDelft\controller\groepen\AbstractGroepenController;
use CsrDelft\controller\groepen\CommissiesController;
use CsrDelft\controller\groepen\KringenController;
use CsrDelft\controller\groepen\OnderverenigingenController;
use CsrDelft\controller\groepen\RechtengroepenController;
use CsrDelft\controller\groepen\WerkgroepenController;
use CsrDelft\controller\groepen\WoonoordenController;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ZoekController {
	public function zoeken(Request $request) {
		$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
		$resultaat = [];

		$instelling = lid_instelling('zoeken', 'leden');
		if ($instelling !== 'nee') {
			$resultaat['Leden'] = (new ToolsController())->naamsuggesties('leden', 'instelling', $zoekterm)->getModel();
		}

		$groepen = [
			'commissies' => CommissiesController::class,
			'kringen' => KringenController::class,
			'onderverenigingen' => OnderverenigingenController::class,
			'werkgroepen' => WerkgroepenController::class,
			'woonoorden' => WoonoordenController::class,
			'groepen' => RechtengroepenController::class,
		];
		foreach ($groepen as $option => $controller) {
			if (lid_instelling('zoeken', $option) === 'ja') {
				/** @var AbstractGroepenController $groepController */
				$groepController = new $controller('');
				$resultaat[ucfirst($option)] = $groepController->zoeken($request, $zoekterm)->getModel();
			}
		}

		if (lid_instelling('zoeken', 'forum') === 'ja') {
			$resultaat['Forum'] = (new ForumController())->titelzoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'fotoalbum') === 'ja') {
			$resultaat['Fotoalbum'] = (new FotoAlbumController())->zoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'agenda') === 'ja') {
			$resultaat['Agenda'] = (new AgendaController())->zoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'documenten') === 'ja') {
			$resultaat['Documenten'] = (new DocumentenController())->zoeken($request, $zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'boeken') === 'ja') {
			$resultaat['Boeken'] = (new BibliotheekController())->zoeken($request, $zoekterm)->getModel();
		}

		return new JsonResponse(array_merge(...array_values($resultaat)));
	}
}
