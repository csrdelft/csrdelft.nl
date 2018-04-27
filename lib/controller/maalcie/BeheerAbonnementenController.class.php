<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\abonnementen\BeheerAbonnementenLijstView;
use CsrDelft\view\maalcie\abonnementen\BeheerAbonnementenView;
use CsrDelft\view\maalcie\abonnementen\BeheerAbonnementView;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property MaaltijdAbonnementenModel $model
 *
 */
class BeheerAbonnementenController extends AclController {
	public function __construct($query) {
		parent::__construct($query, MaaltijdAbonnementenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'waarschuwingen' => 'P_MAAL_MOD',
				'ingeschakeld' => 'P_MAAL_MOD',
				'abonneerbaar' => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'inschakelen' => 'P_MAAL_MOD',
				'uitschakelen' => 'P_MAAL_MOD',
				'novieten' => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'waarschuwingen';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function waarschuwingen() {
		$matrix_repetities = MaaltijdAbonnementenModel::instance()->getAbonnementenWaarschuwingenMatrix();
		$this->view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], true, null);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function ingeschakeld() {
		$matrix_repetities = MaaltijdAbonnementenModel::instance()->getAbonnementenMatrix(true);
		$this->view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], false, true);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function abonneerbaar() {
		$matrix_repetities = MaaltijdAbonnementenModel::instance()->getAbonnementenAbonneerbaarMatrix();
		$this->view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], true, null);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function novieten() {
		$mrid = filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$aantal = $this->model->inschakelenAbonnementVoorNovieten((int)$mrid);
		$matrix = $this->model->getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		$this->view = new BeheerAbonnementenLijstView($matrix);
		setMelding(
			$aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' aangemaakt voor ' .
			$novieten . ' noviet' . ($novieten !== 1 ? 'en' : '') . '.', 1);
	}

	public function inschakelen($mrid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat nie.', $uid));
		}
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $mrid;
		$abo->uid = $uid;
		$aantal = $this->model->inschakelenAbonnement($abo);
		$this->view = new BeheerAbonnementView($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

	public function uitschakelen($mrid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$abo_aantal = $this->model->uitschakelenAbonnement((int)$mrid, $uid);
		$this->view = new BeheerAbonnementView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

}
