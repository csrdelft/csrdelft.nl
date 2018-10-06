<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\leden\LichtingLedenModel;
use CsrDelft\Orm\Entity\T;

/**
 * Lichting.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Lichting extends AbstractGroep {

	const leden = LichtingLedenModel::class;

	/**
	 * Lidjaar
	 * @var int
	 */
	public $lidjaar;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'lidjaar' => array(T::Integer)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lichtingen';

	public function getUrl() {
		return '/groepen/lichtingen/' . $this->lidjaar . '/';
	}

	/**
	 * Read-only: generated group
	 */
	public function mag($action) {
		return $action === AccessAction::Bekijken;
	}

	/**
	 * Read-only: generated group
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		return $action === AccessAction::Bekijken;
	}

}
