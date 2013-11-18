<?php
namespace Taken\CRV;

require_once 'taken/model/TakenModel.class.php';
require_once 'taken/model/PuntenModel.class.php';
require_once 'taken/model/VrijstellingenModel.class.php';
require_once 'taken/view/MijnCorveeView.class.php';
require_once 'taken/view/CorveeRoosterView.class.php';

/**
 * MijnCorveeController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MijnCorveeController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'mijn' => 'P_CORVEE_IK',
				'rooster' => 'P_CORVEE_IK'
			);
		}
		else {
			$this->acl = array(
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
		$taken = TakenModel::getKomendeTakenVoorLid();
		/*$taken = array();
		foreach ($lidtaken as $taak) {
			$datum = strtotime($taak->getDatum());
			$taken = array_merge($taken, TakenModel::getTakenVoorAgenda($datum, $datum, true));
		}*/
		$rooster = TakenModel::getRoosterMatrix($taken);
		$functies = FunctiesModel::getAlleFuncties(true);
		$punten = PuntenModel::loadPuntenVoorLid(\LoginLid::instance()->getLid(), $functies);
		$vrijstelling = VrijstellingenModel::getVrijstelling(\LoginLid::instance()->getUid());
		$this->content = new MijnCorveeView($rooster, $punten, $functies, $vrijstelling);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_rooster() {
		$rooster = TakenModel::getRoosterMatrix(TakenModel::getAlleTaken());
		$this->content = new CorveeRoosterView($rooster);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
}

?>