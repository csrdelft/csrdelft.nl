<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\service\corvee\CorveePuntenService;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnCorveeController {
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CorveeFunctiesRepository
	 */
	private $corveeFunctiesRepository;
	/**
	 * @var CorveeVrijstellingenRepository
	 */
	private $corveeVrijstellingenRepository;
	/**
	 * @var CorveePuntenService
	 */
	private $corveePuntenService;

	public function __construct(CorveeTakenRepository $corveeTakenRepository, CorveeVrijstellingenRepository $corveeVrijstellingenRepository, CorveeFunctiesRepository $corveeFunctiesRepository, CorveePuntenService $corveePuntenService) {
		$this->corveeVrijstellingenRepository = $corveeVrijstellingenRepository;
		$this->corveeFunctiesRepository = $corveeFunctiesRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveePuntenService = $corveePuntenService;
	}

	public function mijn() {
		$taken = $this->corveeTakenRepository->getKomendeTakenVoorLid(LoginModel::getUid());
		$rooster = $this->corveeTakenRepository->getRoosterMatrix($taken);
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$punten = $this->corveePuntenService->loadPuntenVoorLid(LoginModel::getProfiel(), $functies);
		$vrijstelling = $this->corveeVrijstellingenRepository->getVrijstelling(LoginModel::getUid());
		return view('maaltijden.corveetaak.mijn', [
			'rooster' => $rooster,
			'functies' => $functies,
			'punten' => $punten,
			'vrijstelling' => $vrijstelling,
		]);
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' && LoginModel::mag(P_CORVEE_MOD)) {
			$taken = $this->corveeTakenRepository->getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = $this->corveeTakenRepository->getKomendeTaken();
			$toonverleden = LoginModel::mag(P_CORVEE_MOD);
		}
		$rooster = $this->corveeTakenRepository->getRoosterMatrix($taken);
		return view('maaltijden.corveetaak.corvee_rooster', ['rooster' => $rooster, 'toonverleden' => $toonverleden]);
	}

}
