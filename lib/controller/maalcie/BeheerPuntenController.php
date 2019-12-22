<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\maalcie\CorveePuntenModel;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\ProfielModel;

/**
 * BeheerPuntenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BeheerPuntenController {
	/**
	 * @var FunctiesModel
	 */
	private $functiesModel;

	public function __construct(FunctiesModel $functiesModel) {
		$this->functiesModel = $functiesModel;
	}

	public function beheer() {
		$functies = $this->functiesModel->getAlleFuncties(); // grouped by functie_id
		$matrix = CorveePuntenModel::loadPuntenVoorAlleLeden($functies);
		return view('maaltijden.corveepunt.beheer_punten', ['matrix' => $matrix, 'functies' => $functies]);
	}

	public function wijzigpunten($uid) {
		$profiel = ProfielModel::get($uid); // false if lid does not exist
		if (!$profiel) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$punten = (int)filter_input(INPUT_POST, 'totaal_punten', FILTER_SANITIZE_NUMBER_INT);
		CorveePuntenModel::savePuntenVoorLid($profiel, $punten, null);
		$functies = $this->functiesModel->getAlleFuncties(); // grouped by functie_id
		$lijst = CorveePuntenModel::loadPuntenVoorLid($profiel, $functies);
		return view('maaltijden.corveepunt.beheer_punten_lijst', ['puntenlijst' => $lijst]);
	}

	public function wijzigbonus($uid) {
		$profiel = ProfielModel::get($uid); // false if lid does not exist
		if (!$profiel) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$bonus = (int)filter_input(INPUT_POST, 'totaal_bonus', FILTER_SANITIZE_NUMBER_INT);
		CorveePuntenModel::savePuntenVoorLid($profiel, null, $bonus);
		$functies = $this->functiesModel->getAlleFuncties(); // grouped by functie_id
		$lijst = CorveePuntenModel::loadPuntenVoorLid($profiel, $functies);
		return view('maaltijden.corveepunt.beheer_punten_lijst', ['puntenlijst' => $lijst]);
	}

	public function resetjaar() {
		$aantal_taken_errors = CorveePuntenModel::resetCorveejaar();
		$view = $this->beheer();
		$aantal = $aantal_taken_errors[0];
		$taken = $aantal_taken_errors[1];
		setMelding($aantal . ' vrijstelling' . ($aantal !== 1 ? 'en' : '') . ' verwerkt en verwijderd', 1);
		setMelding($taken . ' ta' . ($taken !== 1 ? 'ken' : 'ak') . ' naar de prullenbak verplaatst', 0);
		foreach ($aantal_taken_errors[2] as $error) {
			setMelding($error->getMessage(), -1);
		}

		return $view;
	}

}
