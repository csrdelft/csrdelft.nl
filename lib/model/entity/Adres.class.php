<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Adres.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class Adres extends PersistentEntity {

	/**
	 * @var int
	 */
	public $adres_id;

	/**
	 * @var string
	 */
	public $naam;

	/**
	 * @var string
	 */
	public $straat;

	/**
	 * @var string
	 */
	public $plaats;

	/**
	 * @var int
	 */
	public $huisnummer;

	/**
	 * @var string
	 */
	public $toevoeging;

	/**
	 * @var string
	 */
	public $postcode;

	/**
	 * @var string
	 */
	public $land;

	/**
	 * @var string
	 */
	public $telefoon;

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'adres_id' => [T::Integer, false, 'auto_increment'],
		'naam' => [T::String],
		'straat' => [T::String],
		'plaats' => [T::String],
		'huisnummer' => [T::Integer],
		'toevoeging' => [T::String],
		'postcode' => [T::String],
		'land' => [T::String],
		'telefoon' => [T::String]
	];
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = ['adres_id'];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'adressen';

}
