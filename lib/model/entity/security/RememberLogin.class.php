<?php

/**
 * RememberLogin.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class RememberLogin extends PersistentEntity {

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
		'token'			 => array(T::String),
		'uid'			 => array(T::UID),
		'remember_since' => array(T::DateTime),
		'device_name'	 => array(T::String),
		'ip'			 => array(T::String),
		'lock_ip'		 => array(T::Boolean)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'login_remember';

}
