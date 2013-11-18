<?php
namespace Taken\CRV;

require_once 'taken/model/PuntenModel.class.php';
require_once 'taken/view/BeheerPuntenView.class.php';

/**
 * BeheerPuntenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerPuntenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD',
				'resetjaar' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'wijzigpunten' => 'P_CORVEE_MOD',
				'wijzigbonus' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$crid = null;
		if ($this->hasParam(2)) {
			$crid = intval($this->getParam(2));
		}
		$this->performAction($crid);
	}
	
	public function action_beheer() {
		$functies = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$matrix = PuntenModel::loadPuntenVoorAlleLeden($functies);
		$this->content = new BeheerPuntenView($matrix, $functies);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_wijzigpunten() {
		$lid = \LidCache::getLid($_POST['voor_lid']); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception('Wijzig punten faalt: ongeldig lid; $uid ='. $_POST['voor_lid']);
		}
		$punten = intval($_POST['totaal_punten']);
		PuntenModel::savePuntenVoorLid($lid, $punten, null);
		$functies = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$lijst = PuntenModel::loadPuntenVoorLid($lid, $functies);
		$this->content = new BeheerPuntenView($lijst);
	}
	
	public function action_wijzigbonus() {
		$lid = \LidCache::getLid($_POST['voor_lid']); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception('Wijzig bonus faalt: ongeldig lid; $uid ='. $_POST['voor_lid']);
		}
		$bonus = intval($_POST['totaal_bonus']);
		PuntenModel::savePuntenVoorLid($lid, null, $bonus);
		$functies = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$lijst = PuntenModel::loadPuntenVoorLid($lid, $functies);
		$this->content = new BeheerPuntenView($lijst);
	}
	
	public function action_resetjaar() {
		PuntenModel::resetCorveejaar();
		$this->action_beheer();
	}
}

?>