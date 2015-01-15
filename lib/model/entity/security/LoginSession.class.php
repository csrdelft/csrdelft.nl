<?php

/**
 * LoginSession.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class LoginSession extends PersistentEntity {

	/**
	 * Unique id of the session
	 * @var string
	 */
	public $session_id;
	/**
	 * Lid
	 * @var string
	 */
	public $uid;
	/**
	 * DateTime
	 * @var string
	 */
	public $login_moment;
	/**
	 * DateTime
	 * @var string
	 */
	public $expire;
	/**
	 * User agent
	 * @var string
	 */
	public $user_agent;
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
		'session_id'	 => array(T::String),
		'uid'			 => array(T::UID),
		'login_moment'	 => array(T::DateTime),
		'expire'		 => array(T::DateTime, true),
		'user_agent'	 => array(T::String, true),
		'ip'			 => array(T::String, true),
		'lock_ip'		 => array(T::Boolean)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('session_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'login_sessions';

}
