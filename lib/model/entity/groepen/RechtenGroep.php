<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\leden\RechtenGroepLedenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;

/**
 * RechtenGroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep beperkt voor rechten.
 */
class RechtenGroep extends AbstractGroep {

	const LEDEN = RechtenGroepLedenModel::class;

	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'rechten_aanmelden' => [T::String]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groepen';

	public function getUrl() {
		return '/groepen/overig/' . $this->id;
	}

	/**
	 * Has permission for action?
	 *
	 * @param AccessAction $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {
			case AccessAction::Bekijken:
				break;
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				if (!LoginModel::mag($this->rechten_aanmelden)) {
					return false;
				}
				break;
		}
		return parent::mag($action, $allowedAuthenticationMethods);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		switch ($action) {
			case AccessAction::Aanmaken:
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return true;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

}
