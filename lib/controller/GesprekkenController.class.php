<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\gesprekken\GesprekBerichtenModel;
use CsrDelft\model\gesprekken\GesprekDeelnemersModel;
use CsrDelft\model\gesprekken\GesprekkenModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\gesprekken\BerichtenResponse;
use CsrDelft\view\gesprekken\GesprekBerichtForm;
use CsrDelft\view\gesprekken\GesprekDeelnemerToevoegenForm;
use CsrDelft\view\gesprekken\GesprekForm;
use CsrDelft\view\gesprekken\GesprekkenResponse;
use CsrDelft\view\gesprekken\GesprekkenView;
use CsrDelft\view\JsonResponse;

/**
 * GesprekkenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de chat-functie.
 *
 * @property GesprekkenModel $model
 */
class GesprekkenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, GesprekkenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'web' => P_LOGGED_IN
			);
		} else {
			$this->acl = array(
				'gesprekken' => P_LOGGED_IN,
				'start' => P_LOGGED_IN,
				'toevoegen' => P_LOGGED_IN,
				'zeg' => P_LOGGED_IN,
				'lees' => P_LOGGED_IN,
				'verlaten' => P_LOGGED_IN
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
			if (!$gesprek OR !$deelnemer) {
				$this->exit_http(403);
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
	}

	public function gesprekken() {
		$lastUpdate = (int)filter_input(INPUT_POST, 'lastUpdate', FILTER_SANITIZE_NUMBER_INT);
		$gesprekken = GesprekDeelnemersModel::instance()->getGesprekkenVoorLid(LoginModel::getUid(), $lastUpdate);
		$this->view = new GesprekkenResponse($gesprekken);
	}

	public function start() {
		$form = new GesprekForm();
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['to']);
			if (!$account) {
				$this->exit_http(403);
			}
			$gesprek = $this->model->startGesprek(LoginModel::getAccount(), $account, $values['inhoud']);
			$this->view = new JsonResponse('/gesprekken/web/' . $gesprek->gesprek_id);
		} else {
			$this->view = $form;
		}
	}

	public function toevoegen($gesprek_id = null) {
		if ($gesprek_id === null) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (!isset($selection[0])) {
				$this->exit_http(403);
			}
			$gesprek_id = $selection[0];
		}
		$gesprek = GesprekkenModel::get($gesprek_id);
		$deelnemer = GesprekDeelnemersModel::get($gesprek_id, LoginModel::getUid());
		if (!$gesprek OR !$deelnemer) {
			$this->exit_http(403);
		}
		$form = new GesprekDeelnemerToevoegenForm($gesprek);
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['to']);
			if (!$account) {
				$this->exit_http(403);
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
		if (!$gesprek OR !$deelnemer) {
			$this->exit_http(403);
		}
		$form = new GesprekBerichtForm($gesprek);
		if ($form->validate()) {
			$values = $form->getValues();
			GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $values['inhoud']);
			$lastUpdate = (int)filter_input(INPUT_POST, 'lastUpdate', FILTER_SANITIZE_NUMBER_INT);
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
		if (!$gesprek OR !$deelnemer) {
			$this->exit_http(403);
		}
		$lastUpdate = (int)filter_input(INPUT_POST, 'lastUpdate', FILTER_SANITIZE_NUMBER_INT);
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
		if (!$gesprek OR !$deelnemer) {
			$this->exit_http(403);
		}
		$gesloten = GesprekDeelnemersModel::instance()->verlaatGesprek($gesprek, $deelnemer);
		$response = array();
		if ($gesloten) {
			$response[] = $gesprek;
		}
		$this->view = new RemoveRowsResponse($response);
	}

}
