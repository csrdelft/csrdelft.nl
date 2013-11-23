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
		$uid = null;
		if ($this->hasParam(2)) {
			$uid = $this->getParam(2);
		}
		$this->performAction($uid);
	}
	
	public function action_beheer() {
		$functies = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$matrix = PuntenModel::loadPuntenVoorAlleLeden($functies);
		$this->content = new BeheerPuntenView($matrix, $functies);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_wijzigpunten($uid) {
		$lid = \LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$punten = intval($_POST['totaal_punten']);
		PuntenModel::savePuntenVoorLid($lid, $punten, null);
		$functies = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$lijst = PuntenModel::loadPuntenVoorLid($lid, $functies);
		$this->content = new BeheerPuntenView($lijst);
	}
	
	public function action_wijzigbonus($uid) {
		$lid = \LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$bonus = intval($_POST['totaal_bonus']);
		PuntenModel::savePuntenVoorLid($lid, null, $bonus);
		$functies = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$lijst = PuntenModel::loadPuntenVoorLid($lid, $functies);
		$this->content = new BeheerPuntenView($lijst);
	}
	
	public function action_resetjaar() {
		$aantal_taken_errors = PuntenModel::resetCorveejaar();
		$this->action_beheer();
		$aantal = $aantal_taken_errors[0];
		$taken = $aantal_taken_errors[1];
		$this->content->setMelding($aantal .' vrijstelling'. ($aantal !== 1 ? 'en' : '') .' verwerkt en verwijderd', 1);
		$this->content->setMelding($taken .' ta'. ($taken !== 1 ? 'ken' : 'ak') .' naar de prullenbak verplaatst', 0);
		foreach ($aantal_taken_errors[2] as $error) {
			$this->content->setMelding($error->getMessage());
		}
	}
}

?>