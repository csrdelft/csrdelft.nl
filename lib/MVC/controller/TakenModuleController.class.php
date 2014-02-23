<?php

require_once 'MVC/controller/AclController.abstract.php';

/**
 * TakenModuleController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class TakenModuleController extends AclController {

	public function __construct($query) {
		$query = str_replace('maaltijden/', 'maaltijden', $query);
		$query = str_replace('corvee/', 'corvee', $query);
		parent::__construct($query);
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
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		if ($this->action === 'maaltijden') {
			$this->action = 'maaltijdenketzer';
		} elseif ($this->action === 'corvee') {
			$this->action = 'corveemijn';
		}
		Instellingen::setTemp('taken', 'url', '/' . $this->action);
		$this->performAction(array($query));
	}

	protected function geentoegang() {
		require_once 'MVC/model/CmsPaginaModel.class.php';
		require_once 'MVC/view/CmsPaginaView.class.php';
		if (isPosted()) {
			parent::geentoegang();
		}
		$model = new CmsPaginaModel();
		$body = new CmsPaginaView($model->getPagina('maaltijden'));
		$this->view = new CsrLayoutPage($body);
	}

	public function maaltijdenketzer($query) {
		require_once 'taken/controller/MijnMaaltijdenController.class.php';
		$controller = new MijnMaaltijdenController($query);
		$this->view = $controller->getContent();
	}

	public function maaltijdenlijst($query) {
		$query = str_replace('lijst/', 'ketzer/lijst/', $query);
		$query = str_replace('lijst/sluit/', 'sluit/', $query);
		$this->maaltijdenketzer($query);
	}

	public function maaltijdenbeheer($query) {
		require_once 'taken/controller/BeheerMaaltijdenController.class.php';
		$controller = new BeheerMaaltijdenController($query);
		$this->view = $controller->getContent();
	}

	public function maaltijdenrepetities($query) {
		require_once 'taken/controller/MaaltijdRepetitiesController.class.php';
		$controller = new MaaltijdRepetitiesController($query);
		$this->view = $controller->getContent();
	}

	public function maaltijdenabonnementen($query) {
		require_once 'taken/controller/MijnAbonnementenController.class.php';
		$controller = new MijnAbonnementenController($query);
		$this->view = $controller->getContent();
	}

	public function maaltijdenabonnementenbeheer($query) {
		require_once 'taken/controller/BeheerAbonnementenController.class.php';
		$controller = new BeheerAbonnementenController($query);
		$this->view = $controller->getContent();
	}

	public function maaltijdenmaalciesaldi($query) {
		require_once 'taken/controller/MaalCieSaldiController.class.php';
		$controller = new MaalCieSaldiController($query);
		$this->view = $controller->getContent();
	}

	public function corveemijn($query) {
		Instellingen::setTemp('taken', 'url', str_replace('mijn', '', Instellingen::get('taken', 'url'))); // strip "mijn" from url
		require_once 'taken/controller/MijnCorveeController.class.php';
		$controller = new MijnCorveeController($query);
		$this->view = $controller->getContent();
	}

	public function corveerooster($query) {
		$query = str_replace('//rooster', '/rooster', $query . '/rooster'); // fix trailing slash
		$this->corveemijn($query);
	}

	public function corveebeheer($query) {
		require_once 'taken/controller/BeheerTakenController.class.php';
		$controller = new BeheerTakenController($query);
		$this->view = $controller->getContent();
	}

	public function corveerepetities($query) {
		require_once 'taken/controller/CorveeRepetitiesController.class.php';
		$controller = new CorveeRepetitiesController($query);
		$this->view = $controller->getContent();
	}

	public function corveevoorkeuren($query) {
		require_once 'taken/controller/MijnVoorkeurenController.class.php';
		$controller = new MijnVoorkeurenController($query);
		$this->view = $controller->getContent();
	}

	public function corveevoorkeurenbeheer($query) {
		require_once 'taken/controller/BeheerVoorkeurenController.class.php';
		$controller = new BeheerVoorkeurenController($query);
		$this->view = $controller->getContent();
	}

	public function corveepuntenbeheer($query) {
		require_once 'taken/controller/BeheerPuntenController.class.php';
		$controller = new BeheerPuntenController($query);
		$this->view = $controller->getContent();
	}

	public function corveevrijstellingen($query) {
		require_once 'taken/controller/BeheerVrijstellingenController.class.php';
		$controller = new BeheerVrijstellingenController($query);
		$this->view = $controller->getContent();
	}

	public function corveefuncties($query) {
		require_once 'taken/controller/BeheerFunctiesController.class.php';
		$controller = new BeheerFunctiesController($query);
		$this->view = $controller->getContent();
	}

}

?>