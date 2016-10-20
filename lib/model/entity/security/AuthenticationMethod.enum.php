<?php

/**
 * AuthenticationMethod.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Authentication methods for LoginSession.
 * 
 */
abstract class AuthenticationMethod implements PersistentEnum {

	const url_token = 'ut';
	const cookie_token = 'ct';
	const password_login = 'pl';
	const recent_password_login = 'rpl';
	const password_login_and_one_time_token = 'plaott';

	public static function getTypeOptions() {
		return array(self::url_token, self::cookie_token, self::password_login, self::recent_password_login, self::password_login_and_one_time_token);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::url_token: return 'Private url';
			case self::cookie_token: return 'Auto-login';
			case self::password_login: return 'Normal login';
			case self::recent_password_login: return 'Confirm password';
			case self::password_login_and_one_time_token: return 'Two-step verification (2SV)';
			default: throw new Exception('AuthenticationMethod onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::url_token:
			case self::cookie_token:
			case self::password_login:
			case self::recent_password_login:
			case self::password_login_and_one_time_token:
				return strtoupper($option);
			default: throw new Exception('AuthenticationMethod onbekend');
		}
	}

}

