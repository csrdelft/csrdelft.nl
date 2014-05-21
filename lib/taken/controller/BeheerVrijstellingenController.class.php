<?php

require_once 'taken/model/VrijstellingenModel.class.php';
require_once 'taken/view/BeheerVrijstellingenView.class.php';
require_once 'taken/view/forms/VrijstellingForm.class.php';

/**
 * BeheerVrijstellingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerVrijstellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		} else {
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
		$this->view = new BeheerVrijstellingenView($vrijstellingen);
		$this->view = new CsrLayoutPage($this->getContent());
		$this->view->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('autocomplete/jquery.autocomplete.min.js');
		$this->view->addScript('taken.js');
	}

	public function nieuw() {
		$vrijstelling = new CorveeVrijstelling();
		$this->view = new VrijstellingForm($vrijstelling->getLidId(), $vrijstelling->getBeginDatum(), $vrijstelling->getEindDatum(), $vrijstelling->getPercentage()); // fetches POST values itself
	}

	public function bewerk($uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$vrijstelling = VrijstellingenModel::getVrijstelling($uid);
		$this->view = new VrijstellingForm($vrijstelling->getLidId(), $vrijstelling->getBeginDatum(), $vrijstelling->getEindDatum(), $vrijstelling->getPercentage()); // fetches POST values itself
	}

	public function opslaan($uid = null) {
		if ($uid !== null) {
			$this->bewerk($uid);
		} else {
			$this->view = new VrijstellingForm(); // fetches POST values itself
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$uid = ($values['lid_id'] === '' ? null : $values['lid_id']);
			$vrijstelling = VrijstellingenModel::saveVrijstelling($uid, $values['begin_datum'], $values['eind_datum'], $values['percentage']);
			$this->view = new BeheerVrijstellingView($vrijstelling);
		}
	}

	public function verwijder($uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		VrijstellingenModel::verwijderVrijstelling($uid);
		echo '<tr id="vrijstelling-row-' . $uid . '" class="remove"></tr>';
		exit;
	}

}
