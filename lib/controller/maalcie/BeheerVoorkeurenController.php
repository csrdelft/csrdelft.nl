<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\voorkeuren\BeheerVoorkeurenView;
use CsrDelft\view\maalcie\corvee\voorkeuren\BeheerVoorkeurView;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVoorkeurenController {
	private $model;

	public function __construct() {
		$this->model = CorveeVoorkeurenModel::instance();
	}

	public function beheer() {
		$matrix_repetities = $this->model->getVoorkeurenMatrix();
		$view = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		return new CsrLayoutPage($view);
	}

	public function inschakelen($crid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = $uid;

		$voorkeur = $this->model->inschakelenVoorkeur($voorkeur);
		$voorkeur->setVanUid($voorkeur->getUid());
		return new BeheerVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = (int)$crid;
		$voorkeur->uid = $uid;
		$voorkeur->setVanUid($uid);

		$this->model->uitschakelenVoorkeur($voorkeur);

		$voorkeur->uid = null;
		return new BeheerVoorkeurView($voorkeur);
	}

}
