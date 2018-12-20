<?php

namespace CsrDelft\common;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/12/2018
 */
class GoogleCaptcha {
	/**
	 * Google Captcha verify url
	 */
	const CAPTCHA_URL = "https://www.google.com/recaptcha/api/siteverify";
	/**
	 * Request constants
	 */
	const FIELD_SECRET = 'secret';
	const FIELD_RESPONSE = 'response';
	/**
	 * Settings constants
	 */
	const GOOGLE_INI = 'google.ini';
	const SETTINGS_FIELD_CAPTCHA_SECRET = 'captcha_secret';

	/**
	 * Google Captcha Post variable
	 */
	const G_RECAPTCHA_RESPONSE = 'g-recaptcha-response';

	/**
	 * Verifieer een captcha bij Google.
	 *
	 * @return bool
	 */
	public static function verify() : bool {
		$captcha = filter_input(INPUT_POST, self::G_RECAPTCHA_RESPONSE);
		$ch = curl_init(self::CAPTCHA_URL);

		$fields = array(
			self::FIELD_SECRET => leesConfig(self::GOOGLE_INI, self::SETTINGS_FIELD_CAPTCHA_SECRET, ''),
			self::FIELD_RESPONSE => $captcha
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		curl_close($ch);

		return json_decode($result)->success;
	}
}
