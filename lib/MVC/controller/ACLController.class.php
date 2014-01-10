<?php

require_once 'MVC/controller/Controller.class.php';

/**
 * ACLController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Voor het uitvoeren van de actie wordt gecheckt of het ingelogde lid wel de juiste permissies heeft
 * door middel van een access control list.
 * 
 */
class ACLController extends Controller {

	/**
	 * Example:
	 * $acl = array(
	 * 'mijn' => 'P_LEDEN_READ',
	 * 'beheer' => 'P_LEDEN_MOD',
	 * 'verwijder' => 'P_ADMIN'
	 * );
	 * @see LoginLid->hasPermission()
	 * @var array
	 */
	protected $acl = array();

	protected function hasPermission($action) {
		return array_key_exists($action, $this->acl) && isset($this->acl[$action]) && LoginLid::instance()->hasPermission($this->acl[$action]);
	}

}

?>