<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\persoonlijk\abonnementen\MijnAbonnementenView;
use CsrDelft\view\maalcie\persoonlijk\abonnementen\MijnAbonnementView;

/**
 * MijnAbonnementenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property MaaltijdAbonnementenModel $model
 *
 */
class MijnAbonnementenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, MaaltijdAbonnementenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'mijn' => P_MAAL_IK
			);
		} else {
			$this->acl = array(
				'inschakelen' => P_MAAL_IK,
				'uitschakelen' => P_MAAL_IK
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'mijn';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = (int)$this->getParam(3);
		}
		parent::performAction(array($mrid));
	}

	public function mijn() {
		$abonnementen = $this->model->getAbonnementenVoorLid(LoginModel::getUid(), true, true);
		$this->view = new MijnAbonnementenView($abonnementen);
		$this->view = new CsrLayoutPage($this->view);
	}

	public function inschakelen($mrid) {
		$abo = new MaaltijdAbonnement();
		$abo->mlt_repetitie_id = $mrid;
		$abo->uid = LoginModel::getUid();
		$aantal = $this->model->inschakelenAbonnement($abo);
		$this->view = new MijnAbonnementView($abo);
		if ($aantal > 0) {
			$melding = 'Automatisch aangemeld voor ' . $aantal . ' maaltijd' . ($aantal === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

	public function uitschakelen($mrid) {
		$abo_aantal = $this->model->uitschakelenAbonnement($mrid, LoginModel::getUid());
		$this->view = new MijnAbonnementView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

}
