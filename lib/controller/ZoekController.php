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

class ZoekController {
	public function zoeken() {
		$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
		$resultaat = [];

		$bronnen = [];
		$instelling = lid_instelling('zoeken', 'leden');
		if ($instelling !== 'nee') {
			$bronnen['Leden'] = '/tools/naamsuggesties?zoekin=leden&status=' . $instelling . '&q=';
			$resultaat['Leden'] = (new ToolsController())->naamsuggesties('leden', 'instelling', $zoekterm)->getModel();
		}

		// TODO: bundelen om simultane verbindingen te sparen
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
				$bronnen[ucfirst($option)] = '/groepen/' . $option . '/zoeken?q=';
				/** @var AbstractGroepenController $groepController */
				$groepController = new $controller('');
				$groepController->zoeken($zoekterm);
				$resultaat[ucfirst($option)] = $groepController->getView()->getModel();
			}
		}

		if (lid_instelling('zoeken', 'forum') === 'ja') {
			$bronnen['Forum'] = '/forum/titelzoeken?q=';
			$resultaat['Forum'] = (new ForumController())->titelzoeken($zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'fotoalbum') === 'ja') {
			$bronnen['Fotoalbum'] = '/fotoalbum/zoeken?q=';
			$resultaat['Fotoalbum'] = (new FotoAlbumController())->zoeken($zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'agenda') === 'ja') {
			$bronnen['Agenda'] = '/agenda/zoeken?q=';
			$resultaat['Agenda'] = (new AgendaController())->zoeken($zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'documenten') === 'ja') {
			$bronnen['Documenten'] = '/documenten/zoeken?q=';
			$resultaat['Documenten'] = (new DocumentenController())->zoeken($zoekterm)->getModel();
		}

		if (lid_instelling('zoeken', 'boeken') === 'ja') {
			$bronnen['Boeken'] = '/bibliotheek/zoeken?q=';
			$resultaat['Boeken'] = (new BibliotheekController())->zoeken($zoekterm)->getModel();
		}

		return new JsonResponse(array_merge(...array_values($resultaat)));
	}
}
