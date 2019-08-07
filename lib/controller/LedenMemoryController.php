<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\ledenmemory\LedenMemoryScoreForm;
use CsrDelft\view\ledenmemory\LedenMemoryScoreResponse;

class LedenMemoryController {
	public function memory() {
		$lidstatus = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$lidstatus[] = LidStatus::Overleden;
		$groep = array();
		$leden = null;
		$cheat = isset($_GET['rosebud']);
		$learnmode = isset($_GET['oefenen']);
		switch (isset($_GET['verticale'])) {

			case true:
				$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
				$verticale = false;
				if (strlen($v) == 1) {
					$verticale = VerticalenModel::get($v);
				}
				if (!$verticale) {
					$verticale = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'), null, null, 1)->fetch();
				}
				if ($verticale) {
					$titel = $verticale->naam . ' verticale ledenmemory' . ($learnmode ? ' (oefenen)' : '');
					$groep = $verticale;
					break;
				}
			// fall through

			case false:
				$l = (int)filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
				$min = LichtingenModel::getOudsteLidjaar();
				$max = LichtingenModel::getJongsteLidjaar();
				if ($l < $min OR $l > $max) {
					$l = $max;
				}
				$lichting = LichtingenModel::get($l);
				if ($lichting) {
					$titel = $lichting->lidjaar . ' lichting ledenmemory' . ($learnmode ? ' (oefenen)' : '');
					$groep = $lichting;
				}
		}
		if ($groep instanceof AbstractGroep) {
			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielModel::get($lid->uid);
				if (in_array($profiel->status, $lidstatus)) {
					$leden[] = $profiel;
				}
			}
		}

		if ($leden == null) {
			throw new CsrGebruikerException("Geen geldige groep");
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
		$score = LedenMemoryScoresModel::instance()->nieuw();
		$form = new LedenMemoryScoreForm($score);
		if ($form->validate()) {
			LedenMemoryScoresModel::instance()->create($score);
		}
		return new JsonResponse($score);
	}

	public function memoryscores($groep = null) {
		$parts = explode('@', $groep);
		if (isset($parts[0], $parts[1])) {
			switch ($parts[1]) {
				case 'verticale.csrdelft.nl':
					$groep = VerticalenModel::instance()->retrieveByUUID($groep);
					break;
				case 'lichting.csrdelft.nl':
					$groep = LichtingenModel::get($parts[0]);
					break;
			}
		}
		if ($groep) {
			$data = LedenMemoryScoresModel::instance()->getGroepTopScores($groep);
		} else {
			$data = LedenMemoryScoresModel::instance()->getAllTopScores();
		}
		return new LedenMemoryScoreResponse($data);
	}
}
