<?php

require_once 'model/maalcie/CorveePuntenModel.class.php';
require_once 'model/maalcie/FunctiesModel.class.php';
require_once 'view/maalcie/BeheerPuntenView.class.php';

/**
 * BeheerPuntenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerPuntenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer'	 => 'P_CORVEE_MOD',
				'resetjaar'	 => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'wijzigpunten'	 => 'P_CORVEE_MOD',
				'wijzigbonus'	 => 'P_CORVEE_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$uid = null;
		if ($this->hasParam(3)) {
			$uid = $this->getParam(3);
		}
		parent::performAction(array($uid));
	}

	public function beheer() {
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$matrix = CorveePuntenModel::loadPuntenVoorAlleLeden($functies);
		$this->view = new BeheerPuntenView($matrix, $functies);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function wijzigpunten($uid) {
		$lid = LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof Lid) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$punten = (int) filter_input(INPUT_POST, 'totaal_punten', FILTER_SANITIZE_NUMBER_INT);
		CorveePuntenModel::savePuntenVoorLid($lid, $punten, null);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$lijst = CorveePuntenModel::loadPuntenVoorLid($lid, $functies);
		$this->view = new BeheerPuntenLidView($lijst);
	}

	public function wijzigbonus($uid) {
		$lid = LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof Lid) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$bonus = (int) filter_input(INPUT_POST, 'totaal_bonus', FILTER_SANITIZE_NUMBER_INT);
		CorveePuntenModel::savePuntenVoorLid($lid, null, $bonus);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$lijst = CorveePuntenModel::loadPuntenVoorLid($lid, $functies);
		$this->view = new BeheerPuntenLidView($lijst);
	}

	public function resetjaar() {
		$aantal_taken_errors = CorveePuntenModel::resetCorveejaar();
		$this->beheer();
		$aantal = $aantal_taken_errors[0];
		$taken = $aantal_taken_errors[1];
		setMelding($aantal . ' vrijstelling' . ($aantal !== 1 ? 'en' : '') . ' verwerkt en verwijderd', 1);
		setMelding($taken . ' ta' . ($taken !== 1 ? 'ken' : 'ak') . ' naar de prullenbak verplaatst', 0);
		foreach ($aantal_taken_errors[2] as $error) {
			setMelding($error->getMessage(), -1);
		}
	}

}
