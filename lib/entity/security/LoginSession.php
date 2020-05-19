<?php

namespace CsrDelft\entity\security;

use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * LoginSession.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Table("login_sessions")
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\LoginSessionRepository")
 */
class LoginSession {
	/**
	 * Primary key
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $session_hash;
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $uid;
	/**
	 * DateTime
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $login_moment;
	/**
	 * DateTime
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $expire;
	/**
	 * User agent
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $user_agent;
	/**
	 * IP address
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $ip;
	/**
	 * Sessie koppelen aan ip
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $lock_ip;
	/**
	 * AuthenticationMethod
	 * @var string
	 * @ORM\Column(type="string")
	 * TODO is eigenlijk Authenticationmethod
	 */
	public $authentication_method;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'session_hash' => array(T::StringKey),
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
		$recent = (int)instelling('beveiliging', 'recent_login_seconds');
		if (time() - $this->login_moment->getTimestamp() < $recent) {
			return true;
		}
		return false;
	}

}
