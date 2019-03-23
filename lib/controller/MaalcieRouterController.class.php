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
		$query = str_replace('maaltijden/', 'maaltijden', $query);
		$query = str_replace('corvee/', 'corvee', $query);
		parent::__construct($query, $query); // use model to pass through query
		$this->acl = array(
			'maaltijdenketzer' => P_MAAL_IK,
			'maaltijdenlijst' => P_MAAL_IK, // shortcut
			'maaltijdenbeheer' => P_MAAL_MOD,
			'maaltijdenfiscaat' => P_MAAL_MOD,
			'maaltijdenrepetities' => P_MAAL_MOD,
			'maaltijdenabonnementen' => P_MAAL_IK,
			'maaltijdenabonnementenbeheer' => P_MAAL_MOD,
			'maaltijdenboekjaar' => P_MAAL_SALDI,
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

	public function maaltijdenketzer() {
		return new MijnMaaltijdenController($this->model);
	}

	public function maaltijdenlijst() {
		$this->model = str_replace('lijst/', 'ketzer/lijst/', $this->model);
		$this->model = str_replace('lijst/sluit/', 'sluit/', $this->model);
		return $this->maaltijdenketzer();
	}

	public function maaltijdenbeheer() {
		return new BeheerMaaltijdenController($this->model);
	}

	public function maaltijdenfiscaat() {
		return new MaaltijdenFiscaatController($this->model);
	}

	public function maaltijdenrepetities() {
		return new MaaltijdRepetitiesController($this->model);
	}

	public function maaltijdenabonnementen() {
		return new MijnAbonnementenController($this->model);
	}

	public function maaltijdenabonnementenbeheer() {
		return new BeheerAbonnementenController($this->model);
	}

	public function maaltijdenboekjaar() {
		return new MaalCieBoekjaarController($this->model);
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
