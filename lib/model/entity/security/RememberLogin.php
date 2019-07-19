<?php

namespace CsrDelft\model\entity\security;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * RememberLogin.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class RememberLogin extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Token string
	 * @var string
	 */
	public $token;
	/**
	 * Lidnummer
	 * @var string
	 */
	public $uid;
	/**
	 * DateTime
	 * @var string
	 */
	public $remember_since;
	/**
	 * Device name
	 * @var string
	 */
	public $device_name;
	/**
	 * IP address
	 * @var string
	 */
	public $ip;
	/**
	 * Sessie koppelen aan ip
	 * @var boolean
	 */
	public $lock_ip;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'token' => array(T::String),
		'uid' => array(T::UID),
		'remember_since' => array(T::DateTime),
		'device_name' => array(T::String),
		'ip' => array(T::String),
		'lock_ip' => array(T::Boolean)
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
	protected static $table_name = 'login_remember';

}
