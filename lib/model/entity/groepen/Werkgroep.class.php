<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\leden\WerkgroepDeelnemersModel;
use CsrDelft\model\security\LoginModel;


/**
 * Werkgroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Werkgroep extends Ketzer {

	const LEDEN = WerkgroepDeelnemersModel::class;

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'werkgroepen';

	public function getUrl() {
		return '/groepen/werkgroepen/' . $this->id . '/';
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		if ($action === AccessAction::Aanmaken AND !LoginModel::mag('P_LEDEN_MOD')) {
			return false;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

}
