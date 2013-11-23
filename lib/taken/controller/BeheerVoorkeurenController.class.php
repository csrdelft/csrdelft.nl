<?php
namespace Taken\CRV;

require_once 'taken/model/VoorkeurenModel.class.php';
require_once 'taken/view/BeheerVoorkeurenView.class.php';

/**
 * BeheerVoorkeurenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerVoorkeurenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
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
		$matrix_repetities = VoorkeurenModel::getVoorkeurenMatrix();
		$this->content = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_inschakelen($crid) {
		$uid = $_POST['voor_lid'];
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$abonnement = VoorkeurenModel::inschakelenVoorkeur($crid, $uid);
		$abonnement->setVanLid($abonnement->getLidId());
		$this->content = new BeheerVoorkeurenView($abonnement);
	}
	
	public function action_uitschakelen($crid) {
		$uid = $_POST['voor_lid'];
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		VoorkeurenModel::uitschakelenVoorkeur($crid, $uid);
		$abonnement = new CorveeVoorkeur($crid, null);
		$abonnement->setVanLid($uid);
		$this->content = new BeheerVoorkeurenView($abonnement);
	}
}

?>