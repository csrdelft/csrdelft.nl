<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * LedenMemoryScore.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class LedenMemoryScore extends PersistentEntity {

	/**
	 * Id
	 * @var int
	 */
	public $id;
	/**
	 * Seconden
	 * @var int
	 */
	public $tijd;
	/**
	 * Aantal beurten
	 * @var int
	 */
	public $beurten;
	/**
	 * Aantal goed
	 * @var int
	 */
	public $goed;
	/**
	 * UUID
	 * @var string
	 */
	public $groep;
	/**
	 * Eerlijk verkregen score
	 * @var boolean
	 */
	public $eerlijk;
	/**
	 * Door lidnummer
	 * Foreign key
	 * @var string
	 */
	public $door_uid;
	/**
	 * Behaald op datum en tijd
	 * @var string
	 */
	public $wanneer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'tijd' => array(T::Integer),
		'beurten' => array(T::Integer),
		'goed' => array(T::Integer),
		'groep' => array(T::Text),
		'eerlijk' => array(T::Boolean),
		'door_uid' => array(T::UID),
		'wanneer' => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'memory_scores';

}
