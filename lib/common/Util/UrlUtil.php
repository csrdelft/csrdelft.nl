<?php

namespace CsrDelft\common\Util;

use Exception;

final class UrlUtil
{
	/**
	 * @source http://www.regular-expressions.info/email.html
	 *
	 * @param (int|string)|false|null $email
	 *
	 * @return false|int
	 *
	 * @psalm-param array-key|false|null $email
	 *
	 * @psalm-return 0|1|false
	 */
	public static function email_like($email): int|false
	{
		if (empty($email)) {
			return false;
		}
		return preg_match(
			"/^[a-zA-Z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+(?:[a-zA-Z]{2,})\b$/",
			(string) $email
		);
	}

	/**
	 * @source https://mathiasbynens.be/demo/url-regex
	 *
	 * @param $url
	 *
	 * @return false|int
	 *
	 * @psalm-return 0|1|false
	 */
	public static function url_like(string $url): int|false
	{
		if (empty($url)) {
			return false;
		}
		return preg_match(
			'_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS',
			(string) $url
		);
	}

	/**
	 * @return false|string
	 */
	public static function external_url(string $url, string $label): string|false
	{
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (
			$url &&
			(UrlUtil::url_like($url) ||
				UrlUtil::url_like(HostUtil::getCsrRoot() . $url))
		) {
			if (
				str_starts_with($url, 'http://') ||
				str_starts_with($url, 'https://')
			) {
				$extern = 'target="_blank" rel="noopener"';
			} else {
				$extern = '';
			}
			$result =
				'<a href="' .
				$url .
				'" title="' .
				$url .
				'" ' .
				$extern .
				'>' .
				$label .
				'</a>';
		} else {
			$result = $url;
		}
		return $result;
	}

	// Base64url functies van https://www.php.net/manual/en/function.base64-encode.php#103849
	public static function base64url_encode(string $data): string
	{
		return rtrim(strtr(base64_encode((string) $data), '+/', '-_'), '=');
	}

	public static function base64url_decode(string $data): string
	{
		return base64_decode(
			str_pad(
				strtr($data, '-_', '+/'),
				strlen((string) $data) % 4,
				'=',
				STR_PAD_RIGHT
			)
		);
	}

	/**
	 * Shorthand for a curl request.
	 * @param $url String The url for the request
	 * @param array $options curl options
	 * @return mixed The curl_exec result
	 */
	public static function curl_request(string $url, $options = [])
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt_array($curl, $options);
		$resp = curl_exec($curl);

		if ($resp == false) {
			throw new Exception(curl_error($curl));
		}

		return $resp;
	}
}
