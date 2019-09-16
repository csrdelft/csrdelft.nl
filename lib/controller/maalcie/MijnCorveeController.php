<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\maalcie\CorveePuntenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\CorveeRoosterView;
use CsrDelft\view\maalcie\persoonlijk\MijnCorveeView;


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
		$view = new MijnCorveeView($rooster, $punten, $functies, $vrijstelling);
		return new CsrLayoutPage($view);
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' AND LoginModel::mag(P_CORVEE_MOD)) {
			$taken = $this->model->getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = $this->model->getKomendeTaken();
			$toonverleden = LoginModel::mag(P_CORVEE_MOD);
		}
		$rooster = $this->model->getRoosterMatrix($taken->fetchAll());
		$view = new CorveeRoosterView($rooster, $toonverleden);
		return new CsrLayoutPage($view);
	}

}
