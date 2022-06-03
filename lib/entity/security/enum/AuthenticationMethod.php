<?php

namespace CsrDelft\entity\security\enum;

use CsrDelft\common\Enum;

/**
 * AuthenticationMethod.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Authentication methods for LoginSession.
 */
class AuthenticationMethod extends Enum {

	/**
	 * AuthenticationMethod opties.
	 */
	const url_token = 'ut';
	const cookie_token = 'ct';
	const password_login = 'pl';
	const recent_password_login = 'rpl';
	const password_login_and_one_time_token = 'plaott';
	const temporary = 'temp';
	const impersonate = 'impersonate';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::url_token => 'Private url',
		self::cookie_token => 'Auto-login',
		self::password_login => 'Normal login',
		self::recent_password_login => 'Confirm password',
		self::password_login_and_one_time_token => 'Two-step verification (2SV)',
		self::temporary => 'Tijdelijk',
		self::impersonate => 'Impersonate',
	];
}

