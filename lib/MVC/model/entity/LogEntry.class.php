<?php

/**
 * LogEntry.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class LogEntry extends PersistentEntity {

	/**
	 * Module controller and action with params
	 * @var string
	 */
	public $class_function;
	/**
	 * Dump data
	 * @var LongText
	 */
	public $dump;
	/**
	 * Call trace
	 * @var string
	 */
	public $call_trace;
	/**
	 * DateTime
	 * @var string
	 */
	public $moment;
	/**
	 * UID
	 * @var string
	 */
	public $uid;
	/**
	 * SU UID
	 * @var string
	 */
	public $su_uid;
	/**
	 * IP address
	 * @var string
	 */
	public $ip;
	/**
	 * IP address referer
	 * @var string
	 */
	public $ip_referer;
	/**
	 * Request URI
	 * @var string
	 */
	public $request;
	/**
	 * User agent
	 * @var string
	 */
	public $user_agent;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'class_function' => array(T::String),
		'dump'			 => array(T::LongText, true),
		'call_trace'	 => array(T::Text),
		'moment'		 => array(T::DateTime),
		'uid'			 => array(T::UID, true),
		'su_uid'		 => array(T::UID, true),
		'ip'			 => array(T::String),
		'request'		 => array(T::String),
		'referer'		 => array(T::String, true),
		'user_agent'	 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('class_function', 'moment');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'debug_log';

}
