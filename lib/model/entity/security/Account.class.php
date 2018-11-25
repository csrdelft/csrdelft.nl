<?php

namespace CsrDelft\model\entity\security;

use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Account.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Login account.
 *
 */
class Account extends PersistentEntity {

	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 */
	public $uid;
	/**
	 * Gebruikersnaam
	 * @var string
	 */
	public $username;
	/**
	 * E-mail address
	 * @var string
	 */
	public $email;
	/**
	 * Password hash
	 * @var string
	 */
	public $pass_hash;
	/**
	 * DateTime last change
	 * @var string
	 */
	public $pass_since;
	/**
	 * DateTime last successful login
	 * @var string
	 */
	public $last_login_success;
	/**
	 * DateTime last login attempt
	 * @var string
	 */
	public $last_login_attempt;
	/**
	 * Amount of failed login attempts
	 * @var int
	 */
	public $failed_login_attempts;
	/**
	 * Reden van blokkering
	 * @var string
	 */
	public $blocked_reason;
	/**
	 * RBAC permissions role
	 * @var string
	 */
	public $perm_role;
	/**
	 * RSS & ICAL token
	 * @var string
	 */
	public $private_token;
	/**
	 * DateTime last change
	 * @var string
	 */
	public $private_token_since;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid' => array(T::UID),
		'username' => array(T::StringKey),
		'email' => array(T::String),
		'pass_hash' => array(T::String),
		'pass_since' => array(T::DateTime),
		'last_login_success' => array(T::DateTime, true),
		'last_login_attempt' => array(T::DateTime, true),
		'failed_login_attempts' => array(T::Integer),
		'blocked_reason' => array(T::Text, true),
		'perm_role' => array(T::Enumeration, false, AccessRole::class),
		'private_token' => array(T::String, true),
		'private_token_since' => array(T::DateTime, true)
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
	protected static $table_name = 'accounts';

	public function getProfiel() {
		return ProfielModel::get($this->uid);
	}

	public function hasPrivateToken() {
		return !empty($this->private_token);
	}

	public function getICalLink() {
		$url = CSR_ROOT . '/agenda/ical/';
		if (empty($this->private_token)) {
			return $url . 'csrdelft.ics';
		} else {
			return $url . $this->private_token . '/csrdelft.ics';
		}
	}

	public function getRssLink() {
		$url = CSR_ROOT . '/forum/rss/';
		if (empty($this->private_token)) {
			return $url . 'csrdelft.xml';
		} else {
			return $url . $this->private_token . '/csrdelft.xml';
		}
	}

}
