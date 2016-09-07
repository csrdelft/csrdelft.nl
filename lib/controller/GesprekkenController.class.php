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
				'web' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'gesprekken' => 'P_LOGGED_IN',
				'start'		 => 'P_LOGGED_IN',
				'toevoegen'	 => 'P_LOGGED_IN',
				'zeg'		 => 'P_LOGGED_IN',
				'lees'		 => 'P_LOGGED_IN',
				'verlaten'	 => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'web';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function web($gesprek_id = null) {
		if ($gesprek_id) {
			$gesprek = GesprekkenModel::get($gesprek_id);
			$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
			if (!$gesprek OR ! $deelnemer) {
				$this->geentoegang();
			}
			$deelnemer->gelezen_moment = getDateTime();
			GesprekDeelnemersModel::instance()->update($deelnemer);
		} else {
			$gesprek = null;
		}
		if ($this->hasParam('zoek')) {
			$filter = $this->getParam('zoek');
		} else {
			$filter = null;
		}
		$body = new GesprekkenView($gesprek, $filter);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('gesprekken');
	}

	public function gesprekken() {
		$lastUpdate = (int) filter_input(INPUT_POST, 'lastUpdate', FILTER_SANITIZE_NUMBER_INT);
		$gesprekken = GesprekDeelnemersModel::instance()->getGesprekkenVoorLid(LoginModel::getUid(), $lastUpdate);
		$this->view = new GesprekkenResponse($gesprekken);
	}

	public function start() {
		$form = new GesprekForm();
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['to']);
			if (!$account) {
				$this->geentoegang();
			}
			$gesprek = $this->model->startGesprek(LoginModel::getAccount(), $account, $values['inhoud']);
			$this->view = new JsonResponse('/gesprekken/web/' . $gesprek->id);
		} else {
			$this->view = $form;
		}
	}

	public function toevoegen($gesprek_id = null) {
		if ($gesprek_id === null) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (!isset($selection[0])) {
				$this->geentoegang();
			}
			$gesprek_id = $selection[0];
		}
		$gesprek = GesprekkenModel::get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
		if (!$gesprek OR ! $deelnemer) {
			$this->geentoegang();
		}
		$form = new GesprekDeelnemerToevoegenForm($gesprek);
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['to']);
			if (!$account) {
				$this->geentoegang();
			}
			GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $account, $deelnemer);
			$this->view = new GesprekkenResponse(array($gesprek));
		} else {
			$this->view = $form;
		}
	}

	public function zeg($gesprek_id = null) {
		$gesprek = GesprekkenModel::get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
		if (!$gesprek OR ! $deelnemer) {
			$this->geentoegang();
		}
		$form = new GesprekBerichtForm($gesprek);
		if ($form->validate()) {
			$values = $form->getValues();
			GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $values['inhoud']);
			$lastUpdate = (int) filter_input(INPUT_POST, 'lastUpdate', FILTER_SANITIZE_NUMBER_INT);
			$berichten = $gesprek->getBerichten($deelnemer, $lastUpdate);
			$this->view = new BerichtenResponse($berichten);
			$this->view->autoUpdate = $gesprek->auto_update;
		} else {
			$this->view = $form;
		}
	}

	public function lees($gesprek_id = null) {
		$gesprek = GesprekkenModel::get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
		if (!$gesprek OR ! $deelnemer) {
			$this->geentoegang();
		}
		$lastUpdate = (int) filter_input(INPUT_POST, 'lastUpdate', FILTER_SANITIZE_NUMBER_INT);
		$berichten = $gesprek->getBerichten($deelnemer, $lastUpdate);
		$this->view = new BerichtenResponse($berichten);
		$this->view->autoUpdate = $gesprek->auto_update;
	}

	public function verlaten($gesprek_id = null) {
		if ($gesprek_id === null) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$gesprek_id = $selection[0];
		}
		$gesprek = GesprekkenModel::get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
		if (!$gesprek OR ! $deelnemer) {
			$this->geentoegang();
		}
		$gesloten = GesprekDeelnemersModel::instance()->verlaatGesprek($gesprek, $deelnemer);
		$response = array();
		if ($gesloten) {
			$response[] = $gesprek;
		}
		$this->view = new RemoveRowsResponse($response);
	}

}
