<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;


/**
 * AbstractGroepLid.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een groep.
 *
 */
abstract class AbstractGroepLid extends PersistentEntity {

	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $groep_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	public $uid;
	/**
	 * CommissieFunctie of opmerking bij lidmaatschap
	 * @var CommissieFunctie
	 */
	public $opmerking;
	/**
	 * Datum en tijd van aanmelden
	 * @var string
	 */
	public $lid_sinds;
	/**
	 * Lidnummer van aanmelder
	 * @var string
	 */
	public $door_uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'groep_id' => array(T::Integer),
		'uid' => array(T::UID),
		'opmerking' => array(T::String, true),
		'lid_sinds' => array(T::DateTime),
		'door_uid' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('groep_id', 'uid');

}
