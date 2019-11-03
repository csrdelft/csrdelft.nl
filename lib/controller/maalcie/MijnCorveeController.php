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
	private $model;

	public function __construct() {
		$this->model = CorveeTakenModel::instance();
	}

	public function mijn() {
		$taken = $this->model->getKomendeTakenVoorLid(LoginModel::getUid());
		$rooster = $this->model->getRoosterMatrix($taken->fetchAll());
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$punten = CorveePuntenModel::loadPuntenVoorLid(LoginModel::getProfiel(), $functies);
		$vrijstelling = CorveeVrijstellingenModel::instance()->getVrijstelling(LoginModel::getUid());
		return view('maaltijden.corveetaak.mijn', [
			'rooster' => $rooster,
			'functies' => $functies,
			'punten' => $punten,
			'vrijstelling' => $vrijstelling,
		]);
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' && LoginModel::mag(P_CORVEE_MOD)) {
			$taken = $this->model->getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = $this->model->getKomendeTaken();
			$toonverleden = LoginModel::mag(P_CORVEE_MOD);
		}
		$rooster = $this->model->getRoosterMatrix($taken->fetchAll());
		return view('maaltijden.corveetaak.corvee_rooster', ['rooster' => $rooster, 'toonverleden' => $toonverleden]);
	}

}
