<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * AuthenticationMethod.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Authentication methods for LoginSession.
 * 
 */
abstract class AuthenticationMethod implements PersistentEnum {

	const URL_TOKEN = 'ut';
	const COOKIE_TOKEN = 'ct';
	const PASSWORD_LOGIN = 'pl';
	const RECENT_PASSWORD_LOGIN = 'rpl';
	const PASSWORD_LOGIN_AND_ONE_TIME_TOKEN = 'plaott';

	public static function getTypeOptions() {
		return array(self::URL_TOKEN, self::COOKIE_TOKEN, self::PASSWORD_LOGIN, self::RECENT_PASSWORD_LOGIN, self::PASSWORD_LOGIN_AND_ONE_TIME_TOKEN);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::URL_TOKEN: return 'Private url';
			case self::COOKIE_TOKEN: return 'Auto-login';
			case self::PASSWORD_LOGIN: return 'Normal login';
			case self::RECENT_PASSWORD_LOGIN: return 'Confirm password';
			case self::PASSWORD_LOGIN_AND_ONE_TIME_TOKEN: return 'Two-step verification (2SV)';
			default: throw new Exception('AuthenticationMethod onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::URL_TOKEN:
			case self::COOKIE_TOKEN:
			case self::PASSWORD_LOGIN:
			case self::RECENT_PASSWORD_LOGIN:
			case self::PASSWORD_LOGIN_AND_ONE_TIME_TOKEN:
				return strtoupper($option);
			default: throw new Exception('AuthenticationMethod onbekend');
		}
	}

}

