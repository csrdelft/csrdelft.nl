<?php
require_once 'controller.class.php';

/**
 * aclcontroller.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Een ACL controller controleert de permissies voor het uitvoeren
 * van de actie.
 */
class ACLController extends Controller {

	/**
	 * Example:
	 * array('mijn' => 'P_MAAL_IK', 'beheer' => 'P_MAAL_MOD', 'verwijder' => 'P_MAAL_MOD');
	 * @var array
	 */
	protected $acl;
	
	public function __construct($querystring, $acl=array()) {
		parent::__construct($querystring);
		$this->acl = $acl;
	}
	
	//call the action with optional (indexed array of) parameter(s)
	protected function performAction($args=null) {
		if (!$this->hasPermission()) {
			$this->action = 'geentoegang';
		}
		parent::performAction($args);
	}
	
	protected function action_geentoegang() {
		parent::action_geentoegang();
		if (!parent::isPOSTed()) {
			$this->content = new csrdelft($this->getContent());
		}
	}
	
	protected function hasPermission() {
		return array_key_exists($this->action, $this->acl) && LoginLid::instance()->hasPermission($this->acl[$this->action]);
	}
}

?>