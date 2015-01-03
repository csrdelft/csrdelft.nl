<?php

require_once 'controller/framework/Controller.abstract.php';

/**
 * AclController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Voor het uitvoeren van de actie wordt gecheckt of
 * het ingelogde lid wel de juiste permissies heeft
 * door middel van een access control list.
 * 
 */
abstract class AclController extends Controller {

	/**
	 * Example:
	 * $acl = array(
	 * 		'mijn' => 'P_LEDEN_READ',
	 * 		'beheer' => 'P_LEDEN_MOD',
	 * 		'verwijder' => 'P_ADMIN'
	 * );
	 * @see LoginModel::mag()
	 * @var array
	 */
	protected $acl;

	protected function mag($action, $resource) {
		if (isset($this->acl[$action])) {
			return LoginModel::mag($this->acl[$action]);
		}
		return LoginModel::mag(AccessModel::get(get_class($this), $action, $resource));
	}

}
