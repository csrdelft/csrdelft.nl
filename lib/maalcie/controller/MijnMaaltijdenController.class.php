<?php

require_once 'maalcie/model/MaaltijdenModel.class.php';
require_once 'maalcie/model/MaaltijdAanmeldingenModel.class.php';
require_once 'maalcie/model/CorveeTakenModel.class.php';
require_once 'maalcie/view/MijnMaaltijdenView.class.php';

/**
 * MijnMaaltijdenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MijnMaaltijdenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'ketzer'	 => 'P_MAAL_IK',
				'lijst'		 => 'P_MAAL_IK',
				'aanmelden'	 => 'P_MAAL_IK',
				'afmelden'	 => 'P_MAAL_IK'
			);
		} else {
			$this->acl = array(
				'sluit'		 => 'P_MAAL_IK',
				'aanmelden'	 => 'P_MAAL_IK',
				'afmelden'	 => 'P_MAAL_IK',
				'gasten'	 => 'P_MAAL_IK',
				'opmerking'	 => 'P_MAAL_IK'
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
			$mid = (int) $this->getParam(3);
		}
		parent::performAction(array($mid));
	}

	public function ketzer() {
		$maaltijden = MaaltijdenModel::getKomendeMaaltijdenVoorLid(LoginModel::getUid());
		$aanmeldingen = MaaltijdAanmeldingenModel::getAanmeldingenVoorLid($maaltijden, LoginModel::getUid());
		$this->view = new MijnMaaltijdenView($maaltijden, $aanmeldingen);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet('/layout/css/maalcie');
		$this->view->addScript('/layout/js/maalcie');
	}

	public function lijst($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND ! LoginModel::mag('P_MAAL_MOD')) {
			$this->geentoegang();
			return;
		}
		$aanmeldingen = MaaltijdAanmeldingenModel::getAanmeldingenVoorMaaltijd($maaltijd);
		$taken = CorveeTakenModel::getTakenVoorMaaltijd($mid);
		require_once 'maalcie/view/MaaltijdLijstView.class.php';
		$this->view = new MaaltijdLijstView($maaltijd, $aanmeldingen, $taken);
	}

	public function sluit($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!$maaltijd->magSluiten(LoginModel::getUid()) AND ! LoginModel::mag('P_MAAL_MOD')) {
			$this->geentoegang();
			return;
		}
		MaaltijdenModel::sluitMaaltijd($maaltijd);
		echo '<h2 id="gesloten-melding" class="remove"></div>';
		exit;
	}

	public function aanmelden($mid) {
		$aanmelding = MaaltijdAanmeldingenModel::aanmeldenVoorMaaltijd($mid, LoginModel::getUid(), LoginModel::getUid());
		if ($this->isPosted()) {
			$this->view = new MijnMaaltijdView($aanmelding->getMaaltijd(), $aanmelding);
		} else {
			require_once 'maalcie/view/MaaltijdKetzerView.class.php';
			$this->view = new MaaltijdKetzerView($aanmelding->getMaaltijd(), $aanmelding);
		}
	}

	public function afmelden($mid) {
		$maaltijd = MaaltijdAanmeldingenModel::afmeldenDoorLid($mid, LoginModel::getUid());
		if ($this->isPosted()) {
			$this->view = new MijnMaaltijdView($maaltijd);
		} else {
			require_once 'maalcie/view/MaaltijdKetzerView.class.php';
			$this->view = new MaaltijdKetzerView($maaltijd);
		}
	}

	public function gasten($mid) {
		$gasten = (int) filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = MaaltijdAanmeldingenModel::saveGasten($mid, LoginModel::getUid(), $gasten);
		$this->view = new MijnMaaltijdView($aanmelding->getMaaltijd(), $aanmelding);
	}

	public function opmerking($mid) {
		$opmerking = filter_input(INPUT_POST, 'gasten_eetwens', FILTER_SANITIZE_STRING);
		$aanmelding = MaaltijdAanmeldingenModel::saveGastenEetwens($mid, LoginModel::getUid(), $opmerking);
		$this->view = new MijnMaaltijdView($aanmelding->getMaaltijd(), $aanmelding);
	}

}
