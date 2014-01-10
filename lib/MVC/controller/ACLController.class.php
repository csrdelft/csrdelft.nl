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
abstract class ACLController extends Controller {

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

	protected function hasPermission() {
		return array_key_exists($this->action, $this->acl) && isset($this->acl[$this->action]) && LoginLid::instance()->hasPermission($this->acl[$this->action]);
	}

}

?>