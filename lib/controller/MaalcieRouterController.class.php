<?php

/**
 * MaalcieRouterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Router voor de maalcie module.
 */
class MaalcieRouterController extends AclController {

	public function __construct($query) {
		$query = str_replace('maaltijden/', 'maaltijden', $query);
		$query = str_replace('corvee/', 'corvee', $query);
		parent::__construct($query, $query); // use model to pass through query
		$this->acl = array(
			'maaltijdenketzer'				 => 'P_MAAL_IK',
			'maaltijdenlijst'				 => 'P_MAAL_IK', // shortcut
			'maaltijdenbeheer'				 => 'P_MAAL_MOD',
			'maaltijdenrepetities'			 => 'P_MAAL_MOD',
			'maaltijdenabonnementen'		 => 'P_MAAL_IK',
			'maaltijdenabonnementenbeheer'	 => 'P_MAAL_MOD',
			'maaltijdenmaalciesaldi'		 => 'P_MAAL_SALDI',
			'corveemijn'					 => 'P_CORVEE_IK',
			'corveerooster'					 => 'P_CORVEE_IK', // shortcut
			'corveebeheer'					 => 'P_CORVEE_MOD',
			'corveerepetities'				 => 'P_CORVEE_MOD',
			'corveevoorkeuren'				 => 'P_CORVEE_IK',
			'corveevoorkeurenbeheer'		 => 'P_CORVEE_MOD',
			'corveepuntenbeheer'			 => 'P_CORVEE_MOD',
			'corveevrijstellingen'			 => 'P_CORVEE_MOD',
			'corveefuncties'				 => 'P_CORVEE_MOD'
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

	protected function exit_http($response_code) {
		if ($this->getMethod() == 'POST') {
			parent::exit_http($response_code);
		}
		require_once 'model/CmsPaginaModel.class.php';
		require_once 'view/CmsPaginaView.class.php';
		$body = new CmsPaginaView(CmsPaginaModel::get('maaltijden'));
		$this->view = new CsrLayoutPage($body);
		$this->view->view();
		exit;
	}

	public function maaltijdenketzer() {
		require_once 'controller/maalcie/MijnMaaltijdenController.class.php';
		return new MijnMaaltijdenController($this->model);
	}

	public function maaltijdenlijst() {
		$this->model = str_replace('lijst/', 'ketzer/lijst/', $this->model);
		$this->model = str_replace('lijst/sluit/', 'sluit/', $this->model);
		return $this->maaltijdenketzer();
	}

	public function maaltijdenbeheer() {
		require_once 'controller/maalcie/BeheerMaaltijdenController.class.php';
		return new BeheerMaaltijdenController($this->model);
	}

	public function maaltijdenrepetities() {
		require_once 'controller/maalcie/MaaltijdRepetitiesController.class.php';
		return new MaaltijdRepetitiesController($this->model);
	}

	public function maaltijdenabonnementen() {
		require_once 'controller/maalcie/MijnAbonnementenController.class.php';
		return new MijnAbonnementenController($this->model);
	}

	public function maaltijdenabonnementenbeheer() {
		require_once 'controller/maalcie/BeheerAbonnementenController.class.php';
		return new BeheerAbonnementenController($this->model);
	}

	public function maaltijdenmaalciesaldi() {
		require_once 'controller/maalcie/MaalCieSaldiController.class.php';
		return new MaalCieSaldiController($this->model);
	}

	public function corveemijn() {
		require_once 'controller/maalcie/MijnCorveeController.class.php';
		return new MijnCorveeController($this->model);
	}

	public function corveerooster() {
		$this->model = str_replace('rooster', 'rooster/rooster', $this->model);
		return $this->corveemijn();
	}

	public function corveebeheer() {
		require_once 'controller/maalcie/BeheerTakenController.class.php';
		return new BeheerTakenController($this->model);
	}

	public function corveerepetities() {
		require_once 'controller/maalcie/CorveeRepetitiesController.class.php';
		return new CorveeRepetitiesController($this->model);
	}

	public function corveevoorkeuren() {
		require_once 'controller/maalcie/MijnVoorkeurenController.class.php';
		return new MijnVoorkeurenController($this->model);
	}

	public function corveevoorkeurenbeheer() {
		require_once 'controller/maalcie/BeheerVoorkeurenController.class.php';
		return new BeheerVoorkeurenController($this->model);
	}

	public function corveepuntenbeheer() {
		require_once 'controller/maalcie/BeheerPuntenController.class.php';
		return new BeheerPuntenController($this->model);
	}

	public function corveevrijstellingen() {
		require_once 'controller/maalcie/BeheerVrijstellingenController.class.php';
		return new BeheerVrijstellingenController($this->model);
	}

	public function corveefuncties() {
		require_once 'controller/maalcie/BeheerFunctiesController.class.php';
		return new BeheerFunctiesController($this->model);
	}

}
