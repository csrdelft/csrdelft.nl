<?php
namespace Taken\MLT;

require_once 'taken/model/ConversieModel.class.php';

/**
 * ConversieController.class.php 	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Omzetten van alle oude maaltijd- en corvee-dingen naar het nieuwe systeem.
 * 
 */
class ConversieController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'button' => 'P_ADMIN',
				'clear' => 'P_ADMIN'
			);
		}
		else {
			$this->acl = array(
				'confirm' => 'P_ADMIN'
			);
		}
		$this->action = 'button';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$this->performAction();
	}
	
	public function action_button() {
		echo '<html><body style="text-align:center;margin-top:200px;">';
		echo '<form method="POST" action="'. $GLOBALS['taken_module'] .'/confirm" onsubmit="this.style.display=\'none\';">';
		echo '<input type="image" src="http://plaetjes.csrdelft.nl/knopjes/red_button.gif"';
		echo ' onmousedown="this.src=\'http://plaetjes.csrdelft.nl/knopjes/red_button_pressed.gif\';"';
		echo ' onmouseup="this.src=\'http://plaetjes.csrdelft.nl/knopjes/red_button.gif\';" />';
		echo '</form></body></html>';
		exit();
	}
	
	public function action_clear() {
		if (\LoginLid::instance()->getUid() !== '1137') {
			$this->action_geentoegang();
			return;
		}
		ConversieModel::leegmaken();
		exit();
	}
	
	public function action_confirm() {
		if (\LoginLid::instance()->getUid() !== '1137') {
			$this->action_geentoegang();
			return;
		}
		ConversieModel::leegmaken();
		ConversieModel::converteer();
		exit();
	}
}

?>