<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Instelling.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Een instelling instantie beschrijft een key-value pair voor een module.
 *
 * Bijvoorbeeld:
 *
 * Voor maaltijden-module:
 *  - Standaard maaltijdprijs
 *  - Marge in verband met gasten
 *
 * Voor corvee-module:
 *  - Corveepunten per jaar
 *
 */
class Instelling extends PersistentEntity {

	/**
	 * Shared primary key
	 * @var string
	 */
	public $module;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 */
	public $waarde;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'module' => array(T::String),
		'instelling_id' => array(T::String),
		'waarde' => array(T::Text)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('module', 'instelling_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'instellingen';

}
