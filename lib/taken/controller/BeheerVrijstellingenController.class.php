<?php


require_once 'taken/model/VrijstellingenModel.class.php';
require_once 'taken/view/BeheerVrijstellingenView.class.php';
require_once 'taken/view/forms/VrijstellingFormView.class.php';

/**
 * BeheerVrijstellingenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerVrijstellingenController extends \AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_CORVEE_MOD',
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'verwijder' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$uid = null;
		if ($this->hasParam(3)) {
			$uid = $this->getParam(3);
		}
		$this->performAction(array($uid));
	}
	
	public function beheer() {
		$vrijstellingen = VrijstellingenModel::getAlleVrijstellingen();
		$this->content = new BeheerVrijstellingenView($vrijstellingen);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->content->addScript('taken.js');
	}
	
	public function nieuw() {
		$vrijstelling = new CorveeVrijstelling();
		$this->content = new VrijstellingFormView($vrijstelling->getLidId(), $vrijstelling->getBeginDatum(), $vrijstelling->getEindDatum(), $vrijstelling->getPercentage()); // fetches POST values itself
	}
	
	public function bewerk($uid) {
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$vrijstelling = VrijstellingenModel::getVrijstelling($uid);
		$this->content = new VrijstellingFormView($vrijstelling->getLidId(), $vrijstelling->getBeginDatum(), $vrijstelling->getEindDatum(), $vrijstelling->getPercentage()); // fetches POST values itself
	}
	
	public function opslaan($uid=null) {
		if ($uid !== null) {
			$this->bewerk($uid);
		}
		else {
			$this->content = new VrijstellingFormView(); // fetches POST values itself
		}
		if ($this->content->validate()) {
			$values = $this->content->getValues();
			$uid = ($values['lid_id'] === '' ? null : $values['lid_id']);
			$vrijstelling = VrijstellingenModel::saveVrijstelling($uid, $values['begin_datum'], $values['eind_datum'], $values['percentage']);
			$this->content = new BeheerVrijstellingenView($vrijstelling);
		}
	}
	
	public function verwijder($uid) {
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		VrijstellingenModel::verwijderVrijstelling($uid);
		$this->content = new BeheerVrijstellingenView($uid);
	}
}

?>