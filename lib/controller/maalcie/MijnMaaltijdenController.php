<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdBeoordelingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\maalcie\beheer\MaaltijdLijstView;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use CsrDelft\view\maalcie\persoonlijk\MaaltijdKetzerView;
use CsrDelft\view\maalcie\persoonlijk\MijnMaaltijdenView;
use CsrDelft\view\maalcie\persoonlijk\MijnMaaltijdView;


/**
 * MijnMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property MaaltijdenModel $model
 *
 */
class MijnMaaltijdenController extends AclController {
	public function __construct($query) {
		parent::__construct($query, MaaltijdenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'ketzer' => P_MAAL_IK,
				'lijst' => P_MAAL_IK,
				'aanmelden' => P_MAAL_IK,
				'afmelden' => P_MAAL_IK
			);
		} else {
			$this->acl = array(
				'sluit' => P_MAAL_IK,
				'aanmelden' => P_MAAL_IK,
				'afmelden' => P_MAAL_IK,
				'gasten' => P_MAAL_IK,
				'opmerking' => P_MAAL_IK,
				'beoordeling' => P_MAAL_IK
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'ketzer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mid = null;
		if ($this->hasParam(3)) {
			$mid = (int)$this->getParam(3);
		}
		parent::performAction(array($mid));
	}

	public function ketzer() {
		$maaltijden = $this->model->getKomendeMaaltijdenVoorLid(LoginModel::getUid());
		$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorLid($maaltijden, LoginModel::getUid());
		$timestamp = strtotime(instelling('maaltijden', 'beoordeling_periode'));
		$recent = MaaltijdAanmeldingenModel::instance()->getRecenteAanmeldingenVoorLid(LoginModel::getUid(), $timestamp);
		$this->view = new MijnMaaltijdenView($maaltijden, $aanmeldingen, $recent);
		$this->view = new CsrLayoutPage($this->view);
	}

	public function lijst($mid) {
		$maaltijd = $this->model->getMaaltijd($mid, true);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND !LoginModel::mag(P_MAAL_MOD)) {
			$this->exit_http(403);
			return;
		}
		$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorMaaltijd($maaltijd);
		$taken = CorveeTakenModel::instance()->getTakenVoorMaaltijd($mid)->fetchAll();
		$this->view = new MaaltijdLijstView($maaltijd, $aanmeldingen, $taken);
	}

	public function sluit($mid) {
		$maaltijd = $this->model->getMaaltijd($mid);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND !LoginModel::mag(P_MAAL_MOD)) {
			$this->exit_http(403);
			return;
		}
		$this->model->sluitMaaltijd($maaltijd);
		echo '<h3 id="gesloten-melding" class="remove"></div>';
		exit;
	}

	public function aanmelden($mid) {
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		$aanmelding = MaaltijdAanmeldingenModel::instance()->aanmeldenVoorMaaltijd($maaltijd, LoginModel::getUid(), LoginModel::getUid());
		if ($this->getMethod() == 'POST') {
			$this->view = new MijnMaaltijdView($aanmelding->maaltijd, $aanmelding);
		} else {
			$this->view = new MaaltijdKetzerView($aanmelding->maaltijd, $aanmelding);
		}
	}

	public function afmelden($mid) {
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		MaaltijdAanmeldingenModel::instance()->afmeldenDoorLid($maaltijd, LoginModel::getUid());
		if ($this->getMethod() == 'POST') {
			$this->view = new MijnMaaltijdView($maaltijd);
		} else {
			$this->view = new MaaltijdKetzerView($maaltijd);
		}
	}

	public function gasten($mid) {
		$gasten = (int)filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = MaaltijdAanmeldingenModel::instance()->saveGasten($mid, LoginModel::getUid(), $gasten);
		$this->view = new MijnMaaltijdView($aanmelding->maaltijd, $aanmelding);
	}

	public function opmerking($mid) {
		$opmerking = filter_input(INPUT_POST, 'gasten_eetwens', FILTER_SANITIZE_STRING);
		$aanmelding = MaaltijdAanmeldingenModel::instance()->saveGastenEetwens($mid, LoginModel::getUid(), $opmerking);
		$this->view = new MijnMaaltijdView($aanmelding->maaltijd, $aanmelding);
	}

	public function beoordeling($mid) {
		$maaltijd = $this->model->getMaaltijd($mid);
		$beoordeling = MaaltijdBeoordelingenModel::instance()->find('maaltijd_id = ? AND uid = ?', array($mid, LoginModel::getUid()))->fetch();
		if (!$beoordeling) {
			$beoordeling = MaaltijdBeoordelingenModel::instance()->nieuw($maaltijd);
		}
		$form = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
		if (!$form->validate()) {
			$form = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
		if ($form->validate()) {
			MaaltijdBeoordelingenModel::instance()->update($beoordeling);
		}
		$this->view = new JsonResponse(null);
	}

}
