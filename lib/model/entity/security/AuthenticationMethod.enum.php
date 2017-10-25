<?php

namespace CsrDelft\model\entity\security;

use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * AuthenticationMethod.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Authentication methods for LoginSession.
 */
abstract class AuthenticationMethod extends PersistentEnum {

	/**
	 * AuthenticationMethod opties.
	 */
	const url_token = 'ut';
	const cookie_token = 'ct';
	const password_login = 'pl';
	const recent_password_login = 'rpl';
	const password_login_and_one_time_token = 'plaott';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::url_token => self::url_token,
		self::cookie_token => self::cookie_token,
		self::password_login => self::password_login,
		self::recent_password_login => self::recent_password_login,
		self::password_login_and_one_time_token => self::password_login_and_one_time_token,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::url_token => 'Private url',
		self::cookie_token => 'Auto-login',
		self::password_login => 'Normal login',
		self::recent_password_login => 'Confirm password',
		self::password_login_and_one_time_token => 'Two-step verification (2SV)',
	];

	/**
	 * @param string $option
	 * @return string
	 * @throws CsrException
	 */
	public static function getChar($option) {
		if (isset(static::$supportedChoices[$option])) {
			return strtoupper($option);
		} else {
			throw new CsrException('AuthenticationMethod onbekend');
		}
	}

}

