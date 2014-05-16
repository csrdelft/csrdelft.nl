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
	public $module_action;
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
	public $lid_id;
	/**
	 * SU UID
	 * @var string
	 */
	public $su_id;
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
		'module_action' => array(T::String),
		'call_trace' => array(T::Text),
		'moment' => array(T::DateTime),
		'lid_id' => array(T::UID),
		'su_id' => array(T::UID, true),
		'ip' => array(T::String),
		'request' => array(T::String),
		'referer' => array(T::String, true),
		'user_agent' => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_keys = array('module_action', 'moment');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'debug_log';

}
