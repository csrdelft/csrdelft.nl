<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\maalcie\persoonlijk\abonnementen\MijnAbonnementenView;
use CsrDelft\view\maalcie\persoonlijk\abonnementen\MijnAbonnementView;

/**
 * MijnAbonnementenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnAbonnementenController {
	private $model;

	public function __construct() {
		$this->model = MaaltijdAbonnementenModel::instance();
	}

	public function mijn() {
		$abonnementen = $this->model->getAbonnementenVoorLid(LoginModel::getUid(), true, true);
		$view = new MijnAbonnementenView($abonnementen);
		return view('default', ['content' => $view]);
	}

	public function inschakelen($mrid) {
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $mrid;
		$abo->uid = LoginModel::getUid();
		$aantal = $this->model->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return new MijnAbonnementView($abo);
	}

	public function uitschakelen($mrid) {
		$abo_aantal = $this->model->uitschakelenAbonnement($mrid, LoginModel::getUid());
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return new MijnAbonnementView($abo_aantal[0]);
	}

}
