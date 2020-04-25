<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\corvee\CorveePuntenService;

/**
 * BeheerPuntenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BeheerPuntenController {
	/**
	 * @var CorveeFunctiesRepository
	 */
	private $corveeFunctiesRepository;
	/**
	 * @var CorveePuntenService
	 */
	private $corveePuntenService;

	public function __construct(CorveeFunctiesRepository $corveeFunctiesRepository, CorveePuntenService $corveePuntenService) {
		$this->corveeFunctiesRepository = $corveeFunctiesRepository;
		$this->corveePuntenService = $corveePuntenService;
	}

	public function beheer() {
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$matrix = $this->corveePuntenService->loadPuntenVoorAlleLeden($functies);
		return view('maaltijden.corveepunt.beheer_punten', ['matrix' => $matrix, 'functies' => $functies]);
	}

	public function wijzigpunten($uid) {
		$profiel = ProfielRepository::get($uid); // false if lid does not exist
		if (!$profiel) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$punten = (int)filter_input(INPUT_POST, 'totaal_punten', FILTER_SANITIZE_NUMBER_INT);
		$this->corveePuntenService->savePuntenVoorLid($profiel, $punten, null);
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$corveePuntenOverzicht = $this->corveePuntenService->loadPuntenVoorLid($profiel, $functies);
		return view('maaltijden.corveepunt.beheer_punten_lijst', ['puntenlijst' => $corveePuntenOverzicht]);
	}

	public function wijzigbonus($uid) {
		$profiel = ProfielRepository::get($uid); // false if lid does not exist
		if (!$profiel) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$bonus = (int)filter_input(INPUT_POST, 'totaal_bonus', FILTER_SANITIZE_NUMBER_INT);
		$this->corveePuntenService->savePuntenVoorLid($profiel, null, $bonus);
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$corveePuntenOverzicht = $this->corveePuntenService->loadPuntenVoorLid($profiel, $functies);
		return view('maaltijden.corveepunt.beheer_punten_lijst', ['puntenlijst' => $corveePuntenOverzicht]);
	}

	public function resetjaar() {
		/**
		 * @var int $aantal
		 * @var int $taken
		 * @var CsrGebruikerException[] $errors
		 */
		list($aantal, $taken, $errors) = $this->corveePuntenService->resetCorveejaar();
		$view = $this->beheer();
		setMelding($aantal . ' vrijstelling' . ($aantal !== 1 ? 'en' : '') . ' verwerkt en verwijderd', 1);
		setMelding($taken . ' ta' . ($taken !== 1 ? 'ken' : 'ak') . ' naar de prullenbak verplaatst', 0);
		foreach ($errors as $error) {
			setMelding($error->getMessage(), -1);
		}

		return $view;
	}

}
