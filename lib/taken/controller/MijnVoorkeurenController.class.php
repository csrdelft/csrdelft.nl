<?php
namespace Taken\CRV;

require_once 'taken/model/VoorkeurenModel.class.php';
require_once 'taken/view/MijnVoorkeurenView.class.php';

/**
 * MijnVoorkeurenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MijnVoorkeurenController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'mijn' => 'P_CORVEE_IK'
			);
		}
		else {
			$this->acl = array(
				'inschakelen' => 'P_CORVEE_IK',
				'uitschakelen' => 'P_CORVEE_IK',
				'eetwens' => 'P_CORVEE_IK'
			);
		}
		$this->action = 'mijn';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$crid = null;
		if ($this->hasParam(2)) {
			$crid = intval($this->getParam(2));
		}
		$this->performAction($crid);
	}
	
	public function action_mijn() {
		$voorkeuren = VoorkeurenModel::getVoorkeurenVoorLid(\LoginLid::instance()->getUid());
		$eetwens = VoorkeurenModel::getEetwens(\LoginLid::instance()->getLid());
		$this->content = new MijnVoorkeurenView($voorkeuren, $eetwens);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_inschakelen($crid) {
		$abonnement = VoorkeurenModel::inschakelenVoorkeur($crid, \LoginLid::instance()->getUid());
		$this->content = new MijnVoorkeurenView($abonnement);
	}
	
	public function action_uitschakelen($crid) {
		VoorkeurenModel::uitschakelenVoorkeur($crid, \LoginLid::instance()->getUid());
		$this->content = new MijnVoorkeurenView($crid);
	}
	
	public function action_eetwens() {
		$eetwens = htmlspecialchars($_POST['eetwens']);
		VoorkeurenModel::setEetwens(\LoginLid::instance()->getLid(), $eetwens);
		$this->content = new MijnVoorkeurenView(null, $eetwens);
	}
}

?>