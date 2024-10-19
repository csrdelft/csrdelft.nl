<?php

namespace CsrDelft\common\Util;

final class CryptoUtil
{
	/**
	 * @source http://stackoverflow.com/a/13733588
	 * @param $length
	 * @return string
	 */
	public static function crypto_rand_token($length)
	{
		$token = '';
		$codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
		$codeAlphabet .= '0123456789';
		for ($i = 0; $i < $length; $i++) {
			$token .=
				$codeAlphabet[self::crypto_rand_secure(0, strlen($codeAlphabet))];
		}
		return $token;
	}

	/**
	 * @param $min int
	 * @param $max int
	 *
	 * @return mixed
	 */
	public static function crypto_rand_secure($min, $max)
	{
		$range = $max - $min;
		if ($range < 0) {
			return $min; // not so random...
		}
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd &= $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $min + $rnd;
	}

	/**
	 * Versie van uniqid die het ook normaal op Windows doet. Als uniqid te snel achter elkaar aangeroepen wordt kan
	 * twee keer hetzelfde gereturned worden. Op Windows gebeurt dit eerder.
	 *
	 * Replacet de punt omdat het anders geen javascript identifier kan zijn.
	 *
	 * Heeft de vorm:
	 *  $prefix_f0f0f0f0f0f0f0_00000000
	 *
	 * @param string $prefix
	 * @return string
	 */
	public static function uniqid_safe($prefix = '')
	{
		return str_replace('.', '_', uniqid($prefix, true));
	}
}
