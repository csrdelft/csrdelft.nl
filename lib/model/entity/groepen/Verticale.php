<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\KringenModel;
use CsrDelft\model\groepen\leden\VerticaleLedenModel;
use CsrDelft\Orm\Entity\T;

/**
 * Verticale.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Verticale extends AbstractGroep {

	const LEDEN = VerticaleLedenModel::class;

	/**
	 * Primary key
	 * @var string
	 */
	public $letter;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'letter' => [T::Char]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	public function getUrl() {
		return '/groepen/verticalen/' . $this->letter . '/';
	}

	public function getKringen() {
		return KringenModel::instance()->getKringenVoorVerticale($this);
	}

	/**
	 * Limit functionality: leden generated
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmaken:
			case AccessAction::Wijzigen:
				return parent::mag($action, $allowedAuthenticationMethods);
		}
		return false;
	}

	/**
	 * Limit functionality: leden generated
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmaken:
			case AccessAction::Wijzigen:
				return parent::magAlgemeen($action, $allowedAuthenticationMethods);
		}
		return false;
	}

}
