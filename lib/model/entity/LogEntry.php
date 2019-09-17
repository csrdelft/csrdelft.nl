<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * LogEntry.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class LogEntry extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $ID;
	/**
	 * UID of user or x999
	 * @var string
	 */
	public $uid;
	/**
	 * IP address of user
	 * @var string
	 */
	public $ip;
	/**
	 * Position of user (if enabled)
	 * @var string
	 */
	public $locatie;
	/**
	 * DateTime
	 * @var string
	 */
	public $moment;
	/**
	 * Request URL
	 * @var string
	 */
	public $url;
	/**
	 * HTTP Referer
	 * @var string
	 */
	public $referer;
	/**
	 * User agent
	 * @var string
	 */
	public $useragent;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'ID' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'ip' => array(T::String),
		'locatie' => array(T::String),
		'moment' => array(T::DateTime),
		'url' => array(T::String),
		'referer' => array(T::String),
		'useragent' => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('ID');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'log';

	public function getFormattedReferer() {
		if ($this->referer == '') {
			return '-';
		} else {
			if (preg_match('/google/i', $this->referer)) {
				$iQpos = 2 + strpos($this->referer, 'q=');
				$iLengte = strpos($this->referer, '&') - $iQpos - 3;
				return urldecode(substr($this->referer, $iQpos, $iLengte));
			} else {
				return $this->referer;
			}
		}
	}
}
