<?php


require_once 'taken/model/TakenModel.class.php';
require_once 'taken/model/PuntenModel.class.php';
require_once 'taken/model/VrijstellingenModel.class.php';
require_once 'taken/view/MijnCorveeView.class.php';
require_once 'taken/view/CorveeRoosterView.class.php';

/**
 * MijnCorveeController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MijnCorveeController extends \AclController {

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
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$arg = null;
		if ($this->hasParam(3)) {
			$arg = $this->getParam(3);
		}
		$this->performAction(array($arg));
	}
	
	public function mijn() {
		$taken = TakenModel::getKomendeTakenVoorLid(\LoginLid::instance()->getUid());
		$rooster = TakenModel::getRoosterMatrix($taken);
		$functies = FunctiesModel::getAlleFuncties(true);
		$punten = PuntenModel::loadPuntenVoorLid(\LoginLid::instance()->getLid(), $functies);
		$vrijstelling = VrijstellingenModel::getVrijstelling(\LoginLid::instance()->getUid());
		$this->content = new MijnCorveeView($rooster, $punten, $functies, $vrijstelling);
		$this->content = new csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function rooster($arg=null) {
		$toonverleden = false;
		if ($arg === 'verleden') {
			$taken = TakenModel::getVerledenTaken();
		}
		else {
			$taken = TakenModel::getKomendeTaken();
			if (\LoginLid::instance()->hasPermission('P_CORVEE_MOD')) {
				$toonverleden = true;
			}
		}
		$rooster = TakenModel::getRoosterMatrix($taken);
		$this->content = new CorveeRoosterView($rooster, $toonverleden);
		$this->content = new csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
}

?>