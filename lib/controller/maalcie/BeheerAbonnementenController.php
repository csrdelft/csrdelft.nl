<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\maalcie\abonnementen\BeheerAbonnementenLijstView;
use CsrDelft\view\maalcie\abonnementen\BeheerAbonnementenView;
use CsrDelft\view\maalcie\abonnementen\BeheerAbonnementView;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerAbonnementenController {
	private $model;

	public function __construct($query) {
		$this->model = MaaltijdAbonnementenModel::instance();
	}

	public function waarschuwingen() {
		$matrix_repetities = MaaltijdAbonnementenModel::instance()->getAbonnementenWaarschuwingenMatrix();
		$view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], true, null);
		return view('default', ['content' => $view]);
	}

	public function ingeschakeld() {
		$matrix_repetities = MaaltijdAbonnementenModel::instance()->getAbonnementenMatrix();
		$view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], false, true);
		return view('default', ['content' => $view]);
	}

	public function abonneerbaar() {
		$matrix_repetities = MaaltijdAbonnementenModel::instance()->getAbonnementenAbonneerbaarMatrix();
		$view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], true, null);
		return view('default', ['content' => $view]);
	}

	public function novieten() {
		$mrid = filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$aantal = $this->model->inschakelenAbonnementVoorNovieten((int)$mrid);
		$matrix = $this->model->getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		setMelding(
			$aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' aangemaakt voor ' .
			$novieten . ' noviet' . ($novieten !== 1 ? 'en' : '') . '.', 1);
		return new BeheerAbonnementenLijstView($matrix);
	}

	public function inschakelen($mrid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat nie.', $uid));
		}
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $mrid;
		$abo->uid = $uid;
		$aantal = $this->model->inschakelenAbonnement($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return new BeheerAbonnementView($abo);
	}

	public function uitschakelen($mrid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$abo_aantal = $this->model->uitschakelenAbonnement((int)$mrid, $uid);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
		return new BeheerAbonnementView($abo_aantal[0]);
	}

}
