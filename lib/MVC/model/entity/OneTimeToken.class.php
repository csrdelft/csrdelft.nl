<?php

/**
 * OneTimeToken.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * One time token for two-step verification.
 * 
 */
class OneTimeToken extends PersistentEntity {

	/**
	 * Lid
	 * @var string
	 */
	public $uid;
	/**
	 * Protected action url
	 * @var string
	 */
	public $url;
	/**
	 * OneTimeToken
	 * @var string
	 */
	public $token;
	/**
	 * Moment of expiration
	 * @var string
	 */
	public $expire;
	/**
	 * Is verfied?
	 * @var boolean
	 */
	public $verified;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid'		 => array(T::UID),
		'url'		 => array(T::String),
		'token'		 => array(T::String),
		'verified'	 => array(T::Boolean),
		'expire'	 => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid', 'url');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'onetimetokens';

}
