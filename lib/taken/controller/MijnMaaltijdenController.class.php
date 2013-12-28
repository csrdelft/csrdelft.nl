<?php
namespace Taken\MLT;

require_once 'aclcontroller.class.php';
require_once 'taken/model/MaaltijdenModel.class.php';
require_once 'taken/model/AanmeldingenModel.class.php';
require_once 'taken/view/MijnMaaltijdenView.class.php';

/**
 * MijnMaaltijdenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MijnMaaltijdenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'ketzer' => 'P_MAAL_IK',
				'lijst' => 'P_MAAL_IK',
				'aanmelden' => 'P_MAAL_IK',
				'afmelden' => 'P_MAAL_IK'
			);
		}
		else {
			$this->acl = array(
				'sluit' => 'P_MAAL_IK',
				'aanmelden' => 'P_MAAL_IK',
				'afmelden' => 'P_MAAL_IK',
				'gasten' => 'P_MAAL_IK',
				'opmerking' => 'P_MAAL_IK'
			);
		}
		$this->action = 'ketzer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$mid = null;
		if ($this->hasParam(2)) {
			$mid = intval($this->getParam(2));
		}
		$this->performAction($mid);
	}
	
	public static function magMaaltijdlijstTonen(Maaltijd $maaltijd, $taken=null) {
		if (opConfide() || \LoginLid::instance()->hasPermission('P_MAAL_MOD')) {
			return true;
		}
		if ($taken === null) {
			$taken = \Taken\CRV\TakenModel::getTakenVoorMaaltijd($maaltijd->getMaaltijdId());
		}
		$uid = \LoginLid::instance()->getUid();
		foreach ($taken as $taak) {
			if ($taak->getLidId() === $uid) {
				return $taak;
			}
		}
		return false;
	}
	
	public function action_ketzer() {
		$maaltijden = MaaltijdenModel::getKomendeMaaltijdenVoorLid(\LoginLid::instance()->getUid());
		$aanmeldingen = AanmeldingenModel::getAanmeldingenVoorLid($maaltijden, \LoginLid::instance()->getUid());
		$this->content = new MijnMaaltijdenView($maaltijden, $aanmeldingen);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_lijst($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid, true);
		$taken = \Taken\CRV\TakenModel::getTakenVoorMaaltijd($mid);
		if (!self::magMaaltijdlijstTonen($maaltijd, $taken)) {
			$this->action_geentoegang();
			return;
		}
		$aanmeldingen = AanmeldingenModel::getAanmeldingenVoorMaaltijd($maaltijd);
		require_once 'taken/view/MaaltijdLijstView.class.php';
		$this->content = new MaaltijdLijstView($maaltijd, $aanmeldingen, $taken);
	}
	
	public function action_sluit($mid) {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!self::magMaaltijdlijstTonen($maaltijd)) {
			$this->action_geentoegang();
			return;
		}
		MaaltijdenModel::sluitMaaltijd($maaltijd);
	}
	
	public function action_aanmelden($mid) {
		$aanmelding = AanmeldingenModel::aanmeldenVoorMaaltijd($mid, \LoginLid::instance()->getUid(), \LoginLid::instance()->getUid());
		if (parent::isPOSTed()) {
			$this->content = new MijnMaaltijdenView($aanmelding->getMaaltijd(), $aanmelding);
		}
		else {
			require_once 'taken/view/MaaltijdKetzerView.class.php';
			$this->content = new MaaltijdKetzerView($aanmelding->getMaaltijd(), $aanmelding);
		}
	}
	
	public function action_afmelden($mid) {
		$maaltijd = AanmeldingenModel::afmeldenDoorLid($mid, \LoginLid::instance()->getUid());
		if (parent::isPOSTed()) {
			$this->content = new MijnMaaltijdenView($maaltijd);
		}
		else {
			require_once 'taken/view/MaaltijdKetzerView.class.php';
			$this->content = new MaaltijdKetzerView($maaltijd);
		}
	}
	
	public function action_gasten($mid) {
		$gasten = intval($_POST['aantal_gasten']);
		$aanmelding = AanmeldingenModel::saveGasten($mid, \LoginLid::instance()->getUid(), $gasten);
		$this->content = new MijnMaaltijdenView($aanmelding->getMaaltijd(), $aanmelding);
	}
	
	public function action_opmerking($mid) {
		$opmerking = htmlspecialchars($_POST['gasten_opmerking']);
		$aanmelding = AanmeldingenModel::saveGastenOpmerking($mid, \LoginLid::instance()->getUid(), $opmerking);
		$this->content = new MijnMaaltijdenView($aanmelding->getMaaltijd(), $aanmelding);
	}
}

?>