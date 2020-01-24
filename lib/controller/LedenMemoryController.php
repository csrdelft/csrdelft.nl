<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\Lichting;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\ledenmemory\LedenMemoryScoreForm;
use CsrDelft\view\ledenmemory\LedenMemoryScoreResponse;

class LedenMemoryController {
	/**
	 * @var LedenMemoryScoresModel
	 */
	private $ledenMemoryScoresModel;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var VerticalenModel
	 */
	private $verticalenModel;

	public function __construct(LedenMemoryScoresModel $ledenMemoryScoresModel, ProfielRepository $profielRepository, VerticalenModel $verticalenModel) {
		$this->ledenMemoryScoresModel = $ledenMemoryScoresModel;
		$this->profielRepository = $profielRepository;
		$this->verticalenModel = $verticalenModel;
	}

	public function memory() {
		$lidstatus = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$lidstatus[] = LidStatus::Overleden;
		$leden = null;
		$cheat = isset($_GET['rosebud']);
		$learnmode = isset($_GET['oefenen']);
		$groep = $this->getVerticale() ?? $this->getLichting();
		if ($groep instanceof Verticale) {
			$titel = $groep->naam . ' verticale ledenmemory' . ($learnmode ? ' (oefenen)' : '');
		} else if ($groep instanceof Lichting) {
			$titel = $groep->lidjaar . ' lichting ledenmemory' . ($learnmode ? ' (oefenen)' : '');
		}
		else {
			throw new CsrGebruikerException("Geen geldige groep");
		}
		if ($groep instanceof AbstractGroep) {
			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielRepository::get($lid->uid);
				if (in_array($profiel->status, $lidstatus)) {
					$leden[] = $profiel;
				}
			}
		}



		return view('ledenmemory', [
			'titel' => $titel,
			'groep' => $groep,
			'cheat' => $cheat,
			'learnmode' => $learnmode,
			'leden' => $leden,
		]);
	}

	public function memoryscore() {
		$score = $this->ledenMemoryScoresModel->nieuw();
		$form = new LedenMemoryScoreForm($score);
		if ($form->validate()) {
			$this->ledenMemoryScoresModel->create($score);
		}
		return new JsonResponse($score);
	}

	public function memoryscores($groep = null) {
		$parts = explode('@', $groep);
		if (isset($parts[0], $parts[1])) {
			switch ($parts[1]) {
				case 'verticale.csrdelft.nl':
					$groep = $this->verticalenModel->retrieveByUUID($groep);
					break;
				case 'lichting.csrdelft.nl':
					$groep = LichtingenModel::instance()->get($parts[0]);
					break;
			}
		}
		if ($groep) {
			$data = $this->ledenMemoryScoresModel->getGroepTopScores($groep);
		} else {
			$data = $this->ledenMemoryScoresModel->getAllTopScores();
		}
		return new LedenMemoryScoreResponse($data);
	}

	public function namenleren() {
		// Haal alle (adspirant-/gast-)leden op.
		$toegestaan = implode(', ', array_map(function ($status) {
			return "'{$status}'";
		}, LidStatus::getLidLike()));
		$profielen = $this->profielRepository->ormFind("status IN ({$toegestaan})");

		// Bouw infostructuur.
		$leden = array_map(function($profiel) {
			/** @var $profiel Profiel */
			return [
				'uid' => $profiel->uid,
				'voornaam' => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam' => $profiel->achternaam,
				'lichting' => $profiel->lidjaar,
				'verticale' => $profiel->verticale ? $profiel->getVerticale()->naam : 'Geen',
				'geslacht' => $profiel->geslacht,
				'studie' => $profiel->studie,
			];
		}, array_filter($profielen, function($profiel) {
			/** @var Profiel $profiel */
			$path = $profiel->getPasfotoInternalPath();
			return
				is_zichtbaar($profiel, 'profielfoto', 'intern') &&
				$path !== null;
		}));

		// Laad Vue app.
		return view('namenleren', [
			'leden' => json_encode($leden),
		]);
	}
	/**
	 * @return AbstractGroep|null
	 */
	private function getLichting()
	{
		$l = (int)filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
		$min = LichtingenModel::getOudsteLidjaar();
		$max = LichtingenModel::getJongsteLidjaar();
		if ($l < $min OR $l > $max) {
			$l = $max;
		}
		$lichting = LichtingenModel::instance()->get($l);
		return $lichting ? $lichting : null;
	}

	/**
	 * @return AbstractGroep|null
	 */
	private function getVerticale()
	{
		$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
		if (!$v) {
			return null;
		}
		$verticale = false;
		if (strlen($v) == 1) {
			$verticale = $this->verticalenModel->get($v);
		}
		if (!$verticale) {
			$verticale = $this->verticalenModel->find('naam LIKE ?', array('%' . $v . '%'), null, null, 1)->fetch();
		}
		return $verticale ? $verticale : null;
	}
}
