<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\controller\maalcie\BeheerAbonnementenController;
use CsrDelft\controller\maalcie\BeheerFunctiesController;
use CsrDelft\controller\maalcie\BeheerMaaltijdenController;
use CsrDelft\controller\maalcie\BeheerPuntenController;
use CsrDelft\controller\maalcie\BeheerTakenController;
use CsrDelft\controller\maalcie\BeheerVoorkeurenController;
use CsrDelft\controller\maalcie\BeheerVrijstellingenController;
use CsrDelft\controller\maalcie\CorveeRepetitiesController;
use CsrDelft\controller\maalcie\MaalCieBoekjaarController;
use CsrDelft\controller\maalcie\MaaltijdenFiscaatController;
use CsrDelft\controller\maalcie\MaaltijdRepetitiesController;
use CsrDelft\controller\maalcie\MijnAbonnementenController;
use CsrDelft\controller\maalcie\MijnCorveeController;
use CsrDelft\controller\maalcie\MijnMaaltijdenController;
use CsrDelft\controller\maalcie\MijnVoorkeurenController;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\CsrLayoutPage;

/**
 * MaalcieRouterController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Router voor de maalcie module.
 */
class MaalcieRouterController extends AclController {

	public function __construct($query) {
		$query = str_replace('corvee/', 'corvee', $query);
		parent::__construct($query, $query); // use model to pass through query
		$this->acl = array(
			'corveemijn' => P_CORVEE_IK,
			'corveerooster' => P_CORVEE_IK, // shortcut
			'corveebeheer' => P_CORVEE_MOD,
			'corveerepetities' => P_CORVEE_MOD,
			'corveevoorkeuren' => P_CORVEE_IK,
			'corveevoorkeurenbeheer' => P_CORVEE_MOD,
			'corveepuntenbeheer' => P_CORVEE_MOD,
			'corveevrijstellingen' => P_CORVEE_MOD,
			'corveefuncties' => P_CORVEE_MOD
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		if ($this->action === 'maaltijden') {
			$this->action = 'maaltijdenketzer';
		} elseif ($this->action === 'corvee') {
			$this->action = 'corveemijn';
		}
		if ($this->action === 'corveemijn') {
			define('maalcieUrl', '/corvee'); // strip "mijn" from url
		} else {
			define('maalcieUrl', '/' . $this->action);
		}
		$controller = parent::performAction();
		if ($controller !== null) {
			$controller->performAction();
			$this->view = $controller->getView();
		}
	}

	public function corveemijn() {
		return new MijnCorveeController($this->model);
	}

	public function corveerooster() {
		$this->model = str_replace('rooster', 'rooster/rooster', $this->model);
		return $this->corveemijn();
	}

	public function corveebeheer() {
		return new BeheerTakenController($this->model);
	}

	public function corveerepetities() {
		return new CorveeRepetitiesController($this->model);
	}

	public function corveevoorkeuren() {
		return new MijnVoorkeurenController($this->model);
	}

	public function corveevoorkeurenbeheer() {
		return new BeheerVoorkeurenController($this->model);
	}

	public function corveepuntenbeheer() {
		return new BeheerPuntenController($this->model);
	}

	public function corveevrijstellingen() {
		return new BeheerVrijstellingenController($this->model);
	}

	public function corveefuncties() {
		return new BeheerFunctiesController($this->model);
	}

}
