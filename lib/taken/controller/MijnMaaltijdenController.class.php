<?php

require_once 'MVC/controller/AclController.abstract.php';
require_once 'taken/model/MaaltijdenModel.class.php';
require_once 'taken/model/AanmeldingenModel.class.php';
require_once 'taken/view/MijnMaaltijdenView.class.php';

/**
 * MijnMaaltijdenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MijnMaaltijdenController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPosted()) {
			$this->acl = array(
				'ketzer' => 'P_MAAL_IK',
				'lijst' => 'P_MAAL_IK',
				'aanmelden' => 'P_MAAL_IK',
				'afmelden' => 'P_MAAL_IK'
			);
		} else {
			$this->acl = array(
				'sluit' => 'P_MAAL_IK',
				'aanmelden' => 'P_MAAL_IK',
				'afmelden' => 'P_MAAL_IK',
				'gasten' => 'P_MAAL_IK',
				'opmerking' => 'P_MAAL_IK'
			);
		}
		$this->action = 'ketzer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mid = null;
		if ($this->hasParam(3)) {
			$mid = intval($this->getParam(3));
		}
		$this->performAction(array($mid));
	}

	public static function magMaaltijdlijstTonen(Maaltijd $maaltijd) {
		//$taken = \TakenModel::getTakenVoorMaaltijd($maaltijd->getMaaltijdId());
		// als er meerdere maaltijden op 1 dag zijn en maar 1 kookploeg (een taak kan maar aan 1 maaltijd gekoppeld zijn)
		$taken = \TakenModel::getTakenVoorAgenda($maaltijd->getBeginMoment(), $maaltijd->getBeginMoment());
		$uid = \LoginLid::instance()->getUid();
		foreach ($taken as $taak) {
			if ($taak->getLidId() === $uid && $taak->getMaaltijdId() !== null) { // het moet wel maaltijdcorvee zijn (vanwege op datum hierboven)
				return $taak; // de taak die toegang geeft tot de maaltijdlijst
			}
		}
		if (opConfide() || \LoginLid::instance()->hasPermission('P_MAAL_MOD')) {
			return true;
		}
		return false;
	}

	public function ketzer() {
		$maaltijden = MaaltijdenModel::getKomendeMaaltijdenVoorLid(\LoginLid::instance()->getUid());
		$aanmeldingen = AanmeldingenModel::getAanmeldingenVoorLid($maaltijden, \LoginLid::instance()->getUid());
		$this->view = new MijnMaaltijdenView($maaltijden, $aanmeldingen);
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}

	public function lijst($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
		if (!self::magMaaltijdlijstTonen($maaltijd)) {
			$this->geentoegang();
			return;
		}
		$aanmeldingen = AanmeldingenModel::getAanmeldingenVoorMaaltijd($maaltijd);
		$taken = \TakenModel::getTakenVoorMaaltijd($mid);
		require_once 'taken/view/MaaltijdLijstView.class.php';
		$this->view = new MaaltijdLijstView($maaltijd, $aanmeldingen, $taken);
	}

	public function sluit($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!self::magMaaltijdlijstTonen($maaltijd)) {
			$this->geentoegang();
			return;
		}
		MaaltijdenModel::sluitMaaltijd($maaltijd);
	}

	public function aanmelden($mid) {
		$aanmelding = AanmeldingenModel::aanmeldenVoorMaaltijd($mid, \LoginLid::instance()->getUid(), \LoginLid::instance()->getUid());
		if (parent::isPosted()) {
			$this->view = new MijnMaaltijdenView($aanmelding->getMaaltijd(), $aanmelding);
		} else {
			require_once 'taken/view/MaaltijdKetzerView.class.php';
			$this->view = new MaaltijdKetzerView($aanmelding->getMaaltijd(), $aanmelding);
		}
	}

	public function afmelden($mid) {
		$maaltijd = AanmeldingenModel::afmeldenDoorLid($mid, \LoginLid::instance()->getUid());
		if (parent::isPosted()) {
			$this->view = new MijnMaaltijdenView($maaltijd);
		} else {
			require_once 'taken/view/MaaltijdKetzerView.class.php';
			$this->view = new MaaltijdKetzerView($maaltijd);
		}
	}

	public function gasten($mid) {
		$gasten = (int) filter_input(INPUT_POST, 'aantal_gasten', FILTER_SANITIZE_NUMBER_INT);
		$aanmelding = AanmeldingenModel::saveGasten($mid, \LoginLid::instance()->getUid(), $gasten);
		$this->view = new MijnMaaltijdenView($aanmelding->getMaaltijd(), $aanmelding);
	}

	public function opmerking($mid) {
		$opmerking = filter_input(INPUT_POST, 'gasten_opmerking', FILTER_SANITIZE_STRING);
		$aanmelding = AanmeldingenModel::saveGastenOpmerking($mid, \LoginLid::instance()->getUid(), $opmerking);
		$this->view = new MijnMaaltijdenView($aanmelding->getMaaltijd(), $aanmelding);
	}

}

?>