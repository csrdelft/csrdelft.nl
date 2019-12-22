<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\maalcie\CorveePuntenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\security\LoginModel;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnCorveeController {
	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;
	/**
	 * @var FunctiesModel
	 */
	private $functiesModel;
	/**
	 * @var CorveeVrijstellingenModel
	 */
	private $corveeVrijstellingenModel;

	public function __construct(CorveeTakenModel $corveeTakenModel, CorveeVrijstellingenModel $corveeVrijstellingenModel, FunctiesModel $functiesModel) {
		$this->corveeVrijstellingenModel = $corveeVrijstellingenModel;
		$this->functiesModel = $functiesModel;
		$this->corveeTakenModel = $corveeTakenModel;
	}

	public function mijn() {
		$taken = $this->corveeTakenModel->getKomendeTakenVoorLid(LoginModel::getUid());
		$rooster = $this->corveeTakenModel->getRoosterMatrix($taken->fetchAll());
		$functies = $this->functiesModel->getAlleFuncties(); // grouped by functie_id
		$punten = CorveePuntenModel::loadPuntenVoorLid(LoginModel::getProfiel(), $functies);
		$vrijstelling = $this->corveeVrijstellingenModel->getVrijstelling(LoginModel::getUid());
		return view('maaltijden.corveetaak.mijn', [
			'rooster' => $rooster,
			'functies' => $functies,
			'punten' => $punten,
			'vrijstelling' => $vrijstelling,
		]);
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' && LoginModel::mag(P_CORVEE_MOD)) {
			$taken = $this->corveeTakenModel->getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = $this->corveeTakenModel->getKomendeTaken();
			$toonverleden = LoginModel::mag(P_CORVEE_MOD);
		}
		$rooster = $this->corveeTakenModel->getRoosterMatrix($taken->fetchAll());
		return view('maaltijden.corveetaak.corvee_rooster', ['rooster' => $rooster, 'toonverleden' => $toonverleden]);
	}

}
