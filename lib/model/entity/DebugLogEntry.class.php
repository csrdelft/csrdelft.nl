<?php

/**
 * DebugLogEntry.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class DebugLogEntry extends PersistentEntity {

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
	 * Lidnummer
	 * @var string
	 */
	public $uid;
	/**
	 * Lidnummer of original user
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
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
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
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'debug_log';

}
