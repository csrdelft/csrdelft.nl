<?php


require_once 'taken/model/VoorkeurenModel.class.php';
require_once 'taken/view/BeheerVoorkeurenView.class.php';

/**
 * BeheerVoorkeurenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'inschakelen' => 'P_CORVEE_MOD',
				'uitschakelen' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$crid = null;
		if ($this->hasParam(3)) {
			$crid = intval($this->getParam(3));
		}
		$this->performAction(array($crid));
	}
	
	public function beheer() {
		$matrix_repetities = VoorkeurenModel::getVoorkeurenMatrix();
		$this->view = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}
	
	public function inschakelen($crid) {
		$uid = filter_input(INPUT_POST, 'voor_lid', FILTER_SANITIZE_STRING);
		if (!\Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$abonnement = VoorkeurenModel::inschakelenVoorkeur($crid, $uid);
		$abonnement->setVanLid($abonnement->getLidId());
		$this->view = new BeheerVoorkeurenView($abonnement);
	}
	
	public function uitschakelen($crid) {
		$uid = filter_input(INPUT_POST, 'voor_lid', FILTER_SANITIZE_STRING);
		if (!\Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid ='. $uid);
		}
		VoorkeurenModel::uitschakelenVoorkeur($crid, $uid);
		$abonnement = new CorveeVoorkeur($crid, null);
		$abonnement->setVanLid($uid);
		$this->view = new BeheerVoorkeurenView($abonnement);
	}
}

?>