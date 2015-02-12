<?php

require_once 'model/GesprekkenModel.class.php';
require_once 'view/GesprekkenView.class.php';

/**
 * GesprekkenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de chat-functie.
 */
class GesprekkenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, GesprekkenModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'view' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'gesprekken' => 'P_LOGGED_IN',
				'nieuw'		 => 'P_LOGGED_IN',
				'lees'		 => 'P_LOGGED_IN',
				'zeg'		 => 'P_LOGGED_IN',
				'toevoegen'	 => 'P_LOGGED_IN',
				'sluiten'	 => 'P_LOGGED_IN',
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'view';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function view($gesprek_id = null) {
		$body = new GesprekkenView($this->model, $gesprek_id);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('gesprekken');
	}

	public function gesprekken($timestamp = null) {
		$gesprekken = GesprekDeelnemersModel::instance()->getGesprekkenVoorLid(LoginModel::getUid(), (int) $timestamp);
		$this->view = new DataTableResponse($gesprekken);
	}

	public function lees($gesprek_id = null, $timestamp = null) {
		$gesprek = $this->model->get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get(LoginModel::getUid());
		if (!$gesprek OR ! $deelnemer OR ! is_numeric($timestamp)) {
			$this->geentoegang();
		}
		$berichten = GesprekBerichtenModel::instance()->getBerichtenVoorGesprek($gesprek, (int) $timestamp);
		$this->view = new DataTableResponse($berichten);
	}

	public function nieuw() {
		$gesprek = $this->model->nieuw();
		$form = new GesprekForm($gesprek);
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['to_uid']);
			if (!$account) {
				$this->geentoegang();
			}
			$deelnemer = $this->model->startGesprek($gesprek, LoginModel::getAccount(), $account);
			GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $values['inhoud']);
			$this->view = '/gesprekken/view/' . $gesprek->gesprek_id;
		} else {
			$this->view = $form;
		}
	}

	public function zeg($gesprek_id = null) {
		$gesprek = $this->model->get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get(LoginModel::getUid());
		if (!$gesprek OR ! $deelnemer) {
			$this->geentoegang();
		}
		$form = new GesprekBerichtForm($gesprek);
		if ($form->validate()) {
			$bericht = GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $inhoud);
			$this->view = new DataTableResponse($bericht);
		} else {
			$this->view = $form;
		}
	}

	public function toevoegen($gesprek_id = null, $uid = null) {
		$gesprek = $this->model->get($gesprek_id);
		$account = AccountModel::get($uid);
		if (!$gesprek OR ! $account) {
			$this->geentoegang();
		}
		GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $account);
	}

	public function sluiten($gesprek_id = null) {
		$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
		if (!$deelnemer) {
			$this->geentoegang();
		}
		GesprekDeelnemersModel::instance()->sluitGesprek($deelnemer);
	}

}
