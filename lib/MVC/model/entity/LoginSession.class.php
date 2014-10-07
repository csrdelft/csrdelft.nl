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
	 * UID
	 * @var string
	 */
	public $uid;
	/**
	 * DateTime
	 * @var string
	 */
	public $moment;
	/**
	 * IP address
	 * @var string
	 */
	public $ip;
	/**
	 * User agent
	 * @var string
	 */
	public $user_agent;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'session_id'	 => array(T::String),
		'uid'			 => array(T::UID),
		'last_active'	 => array(T::DateTime),
		'ip'			 => array(T::String),
		'user_agent'	 => array(T::String),
		'locked_to_ip'	 => array(T::Boolean)
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
