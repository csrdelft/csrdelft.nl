<?php
namespace Taken\MLT;
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
				'button' => 'P_ADMIN'
			);
		}
		else {
			$this->acl = array(
				'do' => 'P_ADMIN'
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
		echo '<form method="POST" action="/actueel/taken/conversie/do">';
		echo '<input type="image" src="http://plaetjes.csrdelft.nl/knopjes/red_button.gif"';
		echo ' onmousedown="this.src=\'http://plaetjes.csrdelft.nl/knopjes/red_button_pressed.gif\';"';
		echo ' onmouseup="this.src=\'http://plaetjes.csrdelft.nl/knopjes/red_button.gif\';" />';
		echo '</form></body></html>';
		exit();
	}
	
	//TODO
	public function action_do() {
		if (\LoginLid::instance()->getUid() !== '1137') {
			$this->action_geentoegang();
			return;
		}
		header( 'Content-type: text/html; charset=utf-8' );
		
		$this->show('start conversie');
		sleep(3);
		$this->show('stap 1');
		sleep(3);
		$this->show('stap 2');
		sleep(3);
		$this->show('stap 3');
		sleep(3);
		$this->show('done');
		exit();
	}
	
	private function show($string){ 
		ob_start();
		echo '<br />'. date('H:i:s') .' '. $string;
		ob_end_flush();
		ob_flush();
		flush();
	} 
}

?>