<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ZoekController extends AbstractController {
	/**
	 * @return JsonResponse
	 * @Route("/zoeken", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function zoeken(Request $request) {
		$zoekterm = $request->query->get('q');
		$resultaat = [];

		$instelling = lid_instelling('zoeken', 'leden');
		if ($instelling !== 'nee') {
			$resultaat[] = $this->forward('CsrDelft\controller\ToolsController::naamsuggesties', ['zoekin' => 'leden', 'zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'commissies') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\groepen\CommissiesController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'kringen') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\groepen\KringenController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'onderverenigingen') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\groepen\OnderverenigingenController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'werkgroepen') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\groepen\WerkgroepenController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'woonoorden') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\groepen\WoonoordenController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'groepen') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\groepen\RechtengroepenController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'forum') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\ForumController::titelzoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'fotoalbum') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\FotoAlbumController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'agenda') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\AgendaController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'documenten') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\DocumentenController::zoeken', ['zoekterm' => $zoekterm]);
		}
		if (lid_instelling('zoeken', 'boeken') === 'ja') {
			$resultaat[] = $this->forward('CsrDelft\controller\BibliotheekController::zoeken', ['zoekterm' => $zoekterm]);
		}

		return new JsonResponse(array_merge(...array_values(array_map(function ($response) {
			return json_decode($response->getContent());
		}, $resultaat))));
	}
}
