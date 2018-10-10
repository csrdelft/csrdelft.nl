<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\leden\CommissieLedenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;


/**
 * Commissie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een commissie is een groep waarvan de groepsleden een specifieke functie (kunnen) hebben.
 *
 */
class Commissie extends AbstractGroep {

	const leden = CommissieLedenModel::class;

	/**
	 * (Bestuurs-)Commissie / SjaarCie
	 * @var CommissieSoort
	 */
	public $soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, CommissieSoort::class),
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'commissies';

	public function getUrl() {
		return '/groepen/commissies/' . $this->id . '/';
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods=null, $soort = null) {
		switch ($soort) {

			case CommissieSoort::SjaarCie:
				if (LoginModel::mag('commissie:NovCie')) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

}
