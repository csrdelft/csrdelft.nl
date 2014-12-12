<?php

/**
 * VerifyTimeout.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Counter to prevent brute force attack.
 * 
 */
class VerifyTimeout extends PersistentEntity {

	/**
	 * Lid
	 * @var string
	 */
	public $uid;
	/**
	 * Aantal pogingen
	 * @var string
	 */
	public $count;
	/**
	 * Moment of last try
	 * @var string
	 */
	public $last_try;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid'		 => array(T::UID),
		'count'		 => array(T::Integer),
		'last_try'	 => array(T::DateTime)
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
	protected static $table_name = 'verify_timeout';

}
