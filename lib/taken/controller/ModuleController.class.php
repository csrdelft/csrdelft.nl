<?php
namespace Taken\CRV;

require_once 'aclcontroller.class.php';

/**
 * ModuleController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class ModuleController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		$this->acl = array(
			'maaltijden' => 'P_MAAL_IK',
			'maaltijdenbeheer' => 'P_MAAL_MOD',
			'maaltijdrepetities' => 'P_MAAL_MOD',
			'abonnementen' => 'P_MAAL_IK',
			'abonnementenbeheer' => 'P_MAAL_MOD',
			'maalciesaldi' => 'P_MAAL_SALDI',
			'corvee' => 'P_CORVEE_IK',
			'corveebeheer' => 'P_CORVEE_MOD',
			'corveerepetities' => 'P_CORVEE_MOD',
			'voorkeuren' => 'P_CORVEE_IK',
			'voorkeurenbeheer' => 'P_CORVEE_MOD',
			'puntenbeheer' => 'P_CORVEE_MOD',
			'vrijstellingen' => 'P_CORVEE_MOD',
			'functies' => 'P_CORVEE_MOD',
			'instellingen' => 'P_MAAL_MOD',
			'conversie' => 'P_ADMIN'
		);
		// module
		$this->action = 'maaltijden';
		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		}
		$this->performAction($query);
	}
	
	protected function action_geentoegang() {
		$this->content = new \PaginaContent(new \Pagina('maaltijden'));
		$this->content = new \csrdelft($this->getContent());
	}
	
	public function action_maaltijden($query) {
		require_once 'taken/controller/MijnMaaltijdenController.class.php';
		$controller = new \Taken\MLT\MijnMaaltijdenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijdenbeheer($query) {
		require_once 'taken/controller/BeheerMaaltijdenController.class.php';
		$controller = new \Taken\MLT\BeheerMaaltijdenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maaltijdrepetities($query) {
		require_once 'taken/controller/MaaltijdRepetitiesController.class.php';
		$controller = new \Taken\MLT\MaaltijdRepetitiesController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_abonnementen($query) {
		require_once 'taken/controller/MijnAbonnementenController.class.php';
		$controller = new \Taken\MLT\MijnAbonnementenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_abonnementenbeheer($query) {
		require_once 'taken/controller/BeheerAbonnementenController.class.php';
		$controller = new \Taken\MLT\BeheerAbonnementenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_maalciesaldi($query) {
		require_once 'taken/controller/MaalCieSaldiController.class.php';
		$controller = new \Taken\MLT\MaalCieSaldiController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corvee($query) {
		require_once 'taken/controller/MijnCorveeController.class.php';
		$controller = new \Taken\CRV\MijnCorveeController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corveebeheer($query) {
		require_once 'taken/controller/BeheerTakenController.class.php';
		$controller = new \Taken\CRV\BeheerTakenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_corveerepetities($query) {
		require_once 'taken/controller/CorveeRepetitiesController.class.php';
		$controller = new \Taken\CRV\CorveeRepetitiesController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_voorkeuren($query) {
		require_once 'taken/controller/MijnVoorkeurenController.class.php';
		$controller = new \Taken\CRV\MijnVoorkeurenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_voorkeurenbeheer($query) {
		require_once 'taken/controller/BeheerVoorkeurenController.class.php';
		$controller = new \Taken\CRV\BeheerVoorkeurenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_puntenbeheer($query) {
		require_once 'taken/controller/BeheerPuntenController.class.php';
		$controller = new \Taken\CRV\BeheerPuntenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_vrijstellingen($query) {
		require_once 'taken/controller/BeheerVrijstellingenController.class.php';
		$controller = new \Taken\CRV\BeheerVrijstellingenController($query);
		$this->content = $controller->getContent();
	}
			
	public function action_functies($query) {
		require_once 'taken/controller/BeheerFunctiesController.class.php';
		$controller = new \Taken\CRV\BeheerFunctiesController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_instellingen($query) {
		require_once 'taken/controller/BeheerInstellingenController.class.php';
		$controller = new \Taken\MLT\BeheerInstellingenController($query);
		$this->content = $controller->getContent();
	}
	
	public function action_conversie($query) {
		require_once 'taken/controller/ConversieController.class.php';
		$controller = new \Taken\MLT\ConversieController($query);
		$this->content = $controller->getContent();
	}
}

?>