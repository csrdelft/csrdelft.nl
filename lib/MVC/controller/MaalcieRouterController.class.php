<?php

/**
 * MaalcieRouterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Router voor de taken module.
 */
class MaalcieRouterController extends AclController {

	/**
	 * Pass through query
	 * @var string
	 */
	private $query;

	public function __construct($query) {
		$this->query = $query;
		$this->query = str_replace('maaltijden/', 'maaltijden', $this->query);
		$this->query = str_replace('corvee/', 'corvee', $this->query);
		parent::__construct($this->query);
		$this->acl = array(
			'maaltijdenketzer' => 'P_MAAL_IK',
			'maaltijdenlijst' => 'P_MAAL_IK', // shortcut
			'maaltijdenbeheer' => 'P_MAAL_MOD',
			'maaltijdenrepetities' => 'P_MAAL_MOD',
			'maaltijdenabonnementen' => 'P_MAAL_IK',
			'maaltijdenabonnementenbeheer' => 'P_MAAL_MOD',
			'maaltijdenmaalciesaldi' => 'P_MAAL_SALDI',
			'corveemijn' => 'P_CORVEE_IK',
			'corveerooster' => 'P_CORVEE_IK', // shortcut
			'corveebeheer' => 'P_CORVEE_MOD',
			'corveerepetities' => 'P_CORVEE_MOD',
			'corveevoorkeuren' => 'P_CORVEE_IK',
			'corveevoorkeurenbeheer' => 'P_CORVEE_MOD',
			'corveepuntenbeheer' => 'P_CORVEE_MOD',
			'corveevrijstellingen' => 'P_CORVEE_MOD',
			'corveefuncties' => 'P_CORVEE_MOD'
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
			Instellingen::setTemp('taken', 'url', '/corvee'); // strip "mijn" from url
		} else {
			Instellingen::setTemp('taken', 'url', '/' . $this->action);
		}
		$controller = parent::performAction();
		if ($controller !== null) {
			$controller->performAction();
			$this->view = $controller->getContent();
		}
	}

	protected function geentoegang() {
		require_once 'MVC/model/CmsPaginaModel.class.php';
		require_once 'MVC/view/CmsPaginaView.class.php';
		if (isPosted()) {
			parent::geentoegang();
		}
		$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('maaltijden'));
		$this->view = new CsrLayoutPage($body);
	}

	public function maaltijdenketzer() {
		require_once 'maalcie/controller/MijnMaaltijdenController.class.php';
		return new MijnMaaltijdenController($this->query);
	}

	public function maaltijdenlijst() {
		$this->query = str_replace('lijst/', 'ketzer/lijst/', $this->query);
		$this->query = str_replace('lijst/sluit/', 'sluit/', $this->query);
		return $this->maaltijdenketzer();
	}

	public function maaltijdenbeheer() {
		require_once 'maalcie/controller/BeheerMaaltijdenController.class.php';
		return new BeheerMaaltijdenController($this->query);
	}

	public function maaltijdenrepetities() {
		require_once 'maalcie/controller/MaaltijdRepetitiesController.class.php';
		return new MaaltijdRepetitiesController($this->query);
	}

	public function maaltijdenabonnementen() {
		require_once 'maalcie/controller/MijnAbonnementenController.class.php';
		return new MijnAbonnementenController($this->query);
	}

	public function maaltijdenabonnementenbeheer() {
		require_once 'maalcie/controller/BeheerAbonnementenController.class.php';
		return new BeheerAbonnementenController($this->query);
	}

	public function maaltijdenmaalciesaldi() {
		require_once 'maalcie/controller/MaalCieSaldiController.class.php';
		return new MaalCieSaldiController($this->query);
	}

	public function corveemijn() {
		require_once 'maalcie/controller/MijnCorveeController.class.php';
		return new MijnCorveeController($this->query);
	}

	public function corveerooster() {
		$this->query = str_replace('rooster', 'rooster/rooster', $this->query);
		return $this->corveemijn();
	}

	public function corveebeheer() {
		require_once 'maalcie/controller/BeheerTakenController.class.php';
		return new BeheerTakenController($this->query);
	}

	public function corveerepetities() {
		require_once 'maalcie/controller/CorveeRepetitiesController.class.php';
		return new CorveeRepetitiesController($this->query);
	}

	public function corveevoorkeuren() {
		require_once 'maalcie/controller/MijnVoorkeurenController.class.php';
		return new MijnVoorkeurenController($this->query);
	}

	public function corveevoorkeurenbeheer() {
		require_once 'maalcie/controller/BeheerVoorkeurenController.class.php';
		return new BeheerVoorkeurenController($this->query);
	}

	public function corveepuntenbeheer() {
		require_once 'maalcie/controller/BeheerPuntenController.class.php';
		return new BeheerPuntenController($this->query);
	}

	public function corveevrijstellingen() {
		require_once 'maalcie/controller/BeheerVrijstellingenController.class.php';
		return new BeheerVrijstellingenController($this->query);
	}

	public function corveefuncties() {
		require_once 'MVC/controller/maalcie/BeheerFunctiesController.class.php';
		return new BeheerFunctiesController($this->query);
	}

}
