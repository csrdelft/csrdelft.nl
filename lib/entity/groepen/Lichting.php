<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\leden\LichtingLedenRepository;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lichting.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="LichtingenRepository")
 */
class Lichting extends AbstractGroep {

	const LEDEN = LichtingLedenRepository::class;

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
		return '/groepen/lichtingen/' . $this->lidjaar;
	}

	/**
	 * Read-only: generated group
	 * @param $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		return $action === AccessAction::Bekijken;
	}

	/**
	 * Read-only: generated group
	 * @param $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		return $action === AccessAction::Bekijken;
	}

}
