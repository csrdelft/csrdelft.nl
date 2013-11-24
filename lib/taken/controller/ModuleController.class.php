<?php
namespace Taken\CRV;

require_once 'aclcontroller.class.php';

/**
 * ModuleController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class ModuleController extends \ACLController {

	public $module;
	
	public function __construct($module, $query) {
		parent::__construct($query);
		if ($module === 'maaltijden') {
			$this->acl = array(
				'maaltijden_ketzer' => 'P_MAAL_IK',
				'maaltijden_lijst' => 'P_MAAL_IK', // shortcut
				'maaltijden_beheer' => 'P_MAAL_MOD',
				'maaltijden_repetities' => 'P_MAAL_MOD',
				'maaltijden_abonnementen' => 'P_MAAL_IK',
				'maaltijden_abonnementenbeheer' => 'P_MAAL_MOD',
				'maaltijden_maalciesaldi' => 'P_MAAL_SALDI',
				'maaltijden_instellingen' => 'P_MAAL_MOD'
			);
			$this->action = 'ketzer'; // default
		}
		elseif ($module === 'corvee') {
			$this->acl = array(
				'corvee_' => 'P_CORVEE_IK',
				'corvee_mijn' => 'P_CORVEE_IK', // shortcut
				'corvee_rooster' => 'P_CORVEE_IK', // shortcut
				'corvee_beheer' => 'P_CORVEE_MOD',
				'corvee_repetities' => 'P_CORVEE_MOD',
				'corvee_voorkeuren' => 'P_CORVEE_IK',
				'corvee_voorkeurenbeheer' => 'P_CORVEE_MOD',
				'corvee_puntenbeheer' => 'P_CORVEE_MOD',
				'corvee_vrijstellingen' => 'P_CORVEE_MOD',
				'corvee_functies' => 'P_CORVEE_MOD',
				'corvee_instellingen' => 'P_MAAL_MOD', // shortcut
				'corvee_conversie' => 'P_ADMIN'
			);
			$this->action = ''; // default
		}
		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		}
		$this->module = $module . $this->action;
		$this->action = $module .'_'. $this->action;
	}
	
	public function performAction($args=null) {
		parent::performAction($args);
	}
	
	protected function action_geentoegang() {
		$this->content = new \PaginaContent(new \Pagina('maaltijden'));
		$this->content = new \csrdelft($this->getContent());
	}
	
	public function action_maaltijden_ketzer($query) {
		require_once 'taken/controller/MijnMaaltijdenController.class.php';
		$controller = new \Taken\MLT\MijnMaaltijdenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijden_lijst($query) {
		$query = str_replace('lijst/sluit/', 'sluit/', $query);
		$this->action_maaltijden_ketzer('ketzer/'. $query);
	}
	
	public function action_maaltijden_beheer($query) {
		require_once 'taken/controller/BeheerMaaltijdenController.class.php';
		$controller = new \Taken\MLT\BeheerMaaltijdenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijden_repetities($query) {
		require_once 'taken/controller/MaaltijdRepetitiesController.class.php';
		$controller = new \Taken\MLT\MaaltijdRepetitiesController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijden_abonnementen($query) {
		require_once 'taken/controller/MijnAbonnementenController.class.php';
		$controller = new \Taken\MLT\MijnAbonnementenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijden_abonnementenbeheer($query) {
		require_once 'taken/controller/BeheerAbonnementenController.class.php';
		$controller = new \Taken\MLT\BeheerAbonnementenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijden_maalciesaldi($query) {
		require_once 'taken/controller/MaalCieSaldiController.class.php';
		$controller = new \Taken\MLT\MaalCieSaldiController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_($query) {
		require_once 'taken/controller/MijnCorveeController.class.php';
		$controller = new \Taken\CRV\MijnCorveeController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_mijn($query) {
		$GLOBALS['taken_module'] = str_replace('mijn', '', $GLOBALS['taken_module']);
		$this->action_corvee_('mijn/'. $query);
	}
	
	public function action_corvee_rooster($query) {
		$this->action_corvee_('mijn/'. $query);
	}
	
	public function action_corvee_beheer($query) {
		require_once 'taken/controller/BeheerTakenController.class.php';
		$controller = new \Taken\CRV\BeheerTakenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_repetities($query) {
		require_once 'taken/controller/CorveeRepetitiesController.class.php';
		$controller = new \Taken\CRV\CorveeRepetitiesController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_voorkeuren($query) {
		require_once 'taken/controller/MijnVoorkeurenController.class.php';
		$controller = new \Taken\CRV\MijnVoorkeurenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_voorkeurenbeheer($query) {
		require_once 'taken/controller/BeheerVoorkeurenController.class.php';
		$controller = new \Taken\CRV\BeheerVoorkeurenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_puntenbeheer($query) {
		require_once 'taken/controller/BeheerPuntenController.class.php';
		$controller = new \Taken\CRV\BeheerPuntenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_vrijstellingen($query) {
		require_once 'taken/controller/BeheerVrijstellingenController.class.php';
		$controller = new \Taken\CRV\BeheerVrijstellingenController($query);
		$this->content = $controller->getContent();
	}
			
	public function action_corvee_functies($query) {
		require_once 'taken/controller/BeheerFunctiesController.class.php';
		$controller = new \Taken\CRV\BeheerFunctiesController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_instellingen($query) {
		$GLOBALS['taken_module'] = str_replace('corvee', 'maaltijden', $GLOBALS['taken_module']);
		$this->action_maaltijden_instellingen($query);
	}
	
	public function action_maaltijden_instellingen($query) {
		require_once 'taken/controller/BeheerInstellingenController.class.php';
		$controller = new \Taken\MLT\BeheerInstellingenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee_conversie($query) {
		require_once 'taken/controller/ConversieController.class.php';
		$controller = new \Taken\MLT\ConversieController($query);
		$this->content = $controller->getContent();
	}
}

?>