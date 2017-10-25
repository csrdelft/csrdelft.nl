<?php

namespace CsrDelft\model\entity\security;

use CsrDelft\model\InstellingenModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * LoginSession.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class LoginSession extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $session_hash;
	/**
	 * Lidnummer
	 * Foreign key
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
	 * AuthenticationMethod
	 * @var string
	 */
	public $authentication_method;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'session_hash' => array(T::String),
		'uid' => array(T::UID),
		'login_moment' => array(T::DateTime),
		'expire' => array(T::DateTime),
		'user_agent' => array(T::String),
		'ip' => array(T::String),
		'lock_ip' => array(T::Boolean),
		'authentication_method' => array(T::Enumeration, false, AuthenticationMethod::class)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('session_hash');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'login_sessions';

	public function isRecent() {
		$recent = (int)InstellingenModel::get('beveiliging', 'recent_login_seconds');
		if (time() - strtotime($this->login_moment) < $recent) {
			return true;
		}
		return false;
	}

}
