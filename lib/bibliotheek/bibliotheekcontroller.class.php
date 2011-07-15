<?php
/*
 * bibliotheekcontroller.class.php	|	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 *
 */

require_once 'controller.class.php';
require_once 'bibliotheek/boek.class.php';
require_once 'bibliotheek/catalogus.class.php';

require_once 'bibliotheek/bibliotheekcontent.class.php';

class BibliotheekController extends Controller{

	public $boek;

	public $baseurl='/communicatie/bibliotheek/';

	/*
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($querystring){
		parent::__construct($querystring);

		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.
		if(Loginlid::instance()->hasPermission('P_BIEB_READ')){
			if($this->hasParam(0) AND $this->getParam(0)!=''){
				$this->action=$this->getParam(0);
			}else{
				$this->action='default';
			}
			//niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
			//zonder P_BIEB_MOD, en gebruikers met, zodat bij niet bestaande acties
			//netjes gewoon de catalogus getoond wordt.
			$allow=array('default', 'boek');
			if(LoginLid::instance()->hasPermission('P_BIEB_EDIT')){
				$allow=array_merge($allow, array('bewerkboek'));
			}
			if(LoginLid::instance()->hasPermission('P_BIEB_MOD','groep:BASFCie')){
				$allow=array_merge($allow, array('verwijderboek'));
			}
			if(!in_array($this->action, $allow)){
				$this->action='default';
			}
		}else{
			$this->action='geentoegang';
		}

		$this->performAction();
	}

	/*
	 * Catalogus tonen
	 */
	protected function action_default(){
		$this->content=new BibliotheekCatalogusContent();
	}
}
