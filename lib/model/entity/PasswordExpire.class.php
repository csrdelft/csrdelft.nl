<?php

/**
 * PasswordExpire.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * One time token for two-step verification.
 * 
 */
class PasswordExpire extends PersistentEntity {

	/**
	 * Lid
	 * @var string
	 */
	public $uid;
	/**
	 * Moment of expiration
	 * @var string
	 */
	public $expire;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid'	 => array(T::UID),
		'expire' => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'password_expire';

}
