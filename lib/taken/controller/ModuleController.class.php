<?php

namespace Taken\CRV;

require_once 'MVC/controller/AclController.abstract.php';

/**
 * ModuleController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ModuleController extends \AclController {

	public function __construct($query) {
		parent::__construct($query);
		$module = $this->getParam(0);
		if ($module === 'maaltijden') {
			$this->acl = array(
				'maaltijdenketzer' => 'P_MAAL_IK',
				'maaltijdenlijst' => 'P_MAAL_IK', // shortcut
				'maaltijdenbeheer' => 'P_MAAL_MOD',
				'maaltijdenrepetities' => 'P_MAAL_MOD',
				'maaltijdenabonnementen' => 'P_MAAL_IK',
				'maaltijdenabonnementenbeheer' => 'P_MAAL_MOD',
				'maaltijdenmaalciesaldi' => 'P_MAAL_SALDI',
				'maaltijdeninstellingen' => 'P_MAAL_MOD'
			);
			$this->action = 'ketzer'; // default
		} elseif ($module === 'corvee') {
			$this->acl = array(
				'corveemijn' => 'P_CORVEE_IK',
				'corveerooster' => 'P_CORVEE_IK', // shortcut
				'corveebeheer' => 'P_CORVEE_MOD',
				'corveerepetities' => 'P_CORVEE_MOD',
				'corveevoorkeuren' => 'P_CORVEE_IK',
				'corveevoorkeurenbeheer' => 'P_CORVEE_MOD',
				'corveepuntenbeheer' => 'P_CORVEE_MOD',
				'corveevrijstellingen' => 'P_CORVEE_MOD',
				'corveefuncties' => 'P_CORVEE_MOD',
				'corveeinstellingen' => 'P_MAAL_MOD' // shortcut
			);
			$this->action = 'mijn'; // default
		} else {
			$module = '';
		}
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$GLOBALS['taken_module'] = '/' . $module . $this->action;
		$this->action = $module . $this->action;
		$this->performAction(array($query));
	}

	protected function geentoegang() {
		require_once 'paginacontent.class.php';
		$this->content = new \csrdelft(new \PaginaContent(new \Pagina('maaltijden')));
	}

	public function maaltijdenketzer($query) {
		require_once 'taken/controller/MijnMaaltijdenController.class.php';
		$controller = new \Taken\MLT\MijnMaaltijdenController($query);
		$this->content = $controller->getContent();
	}

	public function maaltijdenlijst($query) {
		$query = str_replace('lijst/sluit/', 'sluit/', $query);
		$this->maaltijdenketzer('ketzer/' . $query);
	}

	public function maaltijdenbeheer($query) {
		require_once 'taken/controller/BeheerMaaltijdenController.class.php';
		$controller = new \Taken\MLT\BeheerMaaltijdenController($query);
		$this->content = $controller->getContent();
	}

	public function maaltijdenrepetities($query) {
		require_once 'taken/controller/MaaltijdRepetitiesController.class.php';
		$controller = new \Taken\MLT\MaaltijdRepetitiesController($query);
		$this->content = $controller->getContent();
	}

	public function maaltijdenabonnementen($query) {
		require_once 'taken/controller/MijnAbonnementenController.class.php';
		$controller = new \Taken\MLT\MijnAbonnementenController($query);
		$this->content = $controller->getContent();
	}

	public function maaltijdenabonnementenbeheer($query) {
		require_once 'taken/controller/BeheerAbonnementenController.class.php';
		$controller = new \Taken\MLT\BeheerAbonnementenController($query);
		$this->content = $controller->getContent();
	}

	public function maaltijdenmaalciesaldi($query) {
		require_once 'taken/controller/MaalCieSaldiController.class.php';
		$controller = new \Taken\MLT\MaalCieSaldiController($query);
		$this->content = $controller->getContent();
	}

	public function corveemijn($query) {
		$GLOBALS['taken_module'] = str_replace('mijn', '', $GLOBALS['taken_module']);
		require_once 'taken/controller/MijnCorveeController.class.php';
		$controller = new \Taken\CRV\MijnCorveeController($query);
		$this->content = $controller->getContent();
	}

	public function corveerooster($query) {
		$this->corveemijn('mijn/' . $query);
	}

	public function corveebeheer($query) {
		require_once 'taken/controller/BeheerTakenController.class.php';
		$controller = new \Taken\CRV\BeheerTakenController($query);
		$this->content = $controller->getContent();
	}

	public function corveerepetities($query) {
		require_once 'taken/controller/CorveeRepetitiesController.class.php';
		$controller = new \Taken\CRV\CorveeRepetitiesController($query);
		$this->content = $controller->getContent();
	}

	public function corveevoorkeuren($query) {
		require_once 'taken/controller/MijnVoorkeurenController.class.php';
		$controller = new \Taken\CRV\MijnVoorkeurenController($query);
		$this->content = $controller->getContent();
	}

	public function corveevoorkeurenbeheer($query) {
		require_once 'taken/controller/BeheerVoorkeurenController.class.php';
		$controller = new \Taken\CRV\BeheerVoorkeurenController($query);
		$this->content = $controller->getContent();
	}

	public function corveepuntenbeheer($query) {
		require_once 'taken/controller/BeheerPuntenController.class.php';
		$controller = new \Taken\CRV\BeheerPuntenController($query);
		$this->content = $controller->getContent();
	}

	public function corveevrijstellingen($query) {
		require_once 'taken/controller/BeheerVrijstellingenController.class.php';
		$controller = new \Taken\CRV\BeheerVrijstellingenController($query);
		$this->content = $controller->getContent();
	}

	public function corveefuncties($query) {
		require_once 'taken/controller/BeheerFunctiesController.class.php';
		$controller = new \Taken\CRV\BeheerFunctiesController($query);
		$this->content = $controller->getContent();
	}

	public function corveeinstellingen($query) {
		$GLOBALS['taken_module'] = str_replace('corvee', 'maaltijden', $GLOBALS['taken_module']);
		$this->maaltijdeninstellingen($query);
	}

	public function maaltijdeninstellingen($query) {
		require_once 'taken/controller/BeheerInstellingenController.class.php';
		$controller = new \Taken\MLT\BeheerInstellingenController($query);
		$this->content = $controller->getContent();
	}

}

?>