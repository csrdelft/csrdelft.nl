<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\maalcie\CorveePuntenModel;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\punten\BeheerPuntenLidView;
use CsrDelft\view\maalcie\corvee\punten\BeheerPuntenView;

/**
 * BeheerPuntenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BeheerPuntenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD',
				'resetjaar' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'wijzigpunten' => 'P_CORVEE_MOD',
				'wijzigbonus' => 'P_CORVEE_MOD'
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
	}

	public function wijzigpunten($uid) {
		$profiel = ProfielModel::get($uid); // false if lid does not exist
		if (!$profiel) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$punten = (int)filter_input(INPUT_POST, 'totaal_punten', FILTER_SANITIZE_NUMBER_INT);
		CorveePuntenModel::savePuntenVoorLid($profiel, $punten, null);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$lijst = CorveePuntenModel::loadPuntenVoorLid($profiel, $functies);
		$this->view = new BeheerPuntenLidView($lijst);
	}

	public function wijzigbonus($uid) {
		$profiel = ProfielModel::get($uid); // false if lid does not exist
		if (!$profiel) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$bonus = (int)filter_input(INPUT_POST, 'totaal_bonus', FILTER_SANITIZE_NUMBER_INT);
		CorveePuntenModel::savePuntenVoorLid($profiel, null, $bonus);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$lijst = CorveePuntenModel::loadPuntenVoorLid($profiel, $functies);
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
