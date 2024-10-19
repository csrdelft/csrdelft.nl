<?php

namespace CsrDelft\common\Util;

use Exception;

final class UrlUtil
{
	/**
	 * @source http://www.regular-expressions.info/email.html
	 * @param $email
	 *
	 * @return bool
	 */
	public static function email_like($email)
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
	 * @param $url
	 *
	 * @return bool
	 */
	public static function url_like($url)
	{
		if (empty($url)) {
			return false;
		}
		return preg_match(
			'_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS',
			(string) $url
		);
	}

	public static function external_url($url, $label)
	{
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (
			$url &&
			(UrlUtil::url_like($url) ||
				UrlUtil::url_like(HostUtil::getCsrRoot() . $url))
		) {
			$extern =
				str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
					? 'target="_blank" rel="noopener"'
					: '';
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

	public static function url2absolute($baseurl, $relativeurl)
	{
		// if the relative URL is scheme relative then treat it differently
		if (str_starts_with((string) $relativeurl, '//')) {
			if (parse_url((string) $baseurl, PHP_URL_SCHEME) != null) {
				return parse_url((string) $baseurl, PHP_URL_SCHEME) .
					':' .
					$relativeurl;
			} else {
				// assume HTTP
				return 'http:' . $relativeurl;
			}
		}

		// if the relative URL points to the root then treat it more simply
		if (str_starts_with((string) $relativeurl, '/')) {
			$parts = parse_url((string) $baseurl);
			$return = $parts['scheme'] . ':';
			$return .= $parts['scheme'] === 'file' ? '///' : '//';
			// username:password@host:port ... could go here too!
			$return .= $parts['host'] . $relativeurl;
			return $return;
		}

		// If the relative URL is actually an absolute URL then just use that
		if (parse_url((string) $relativeurl, PHP_URL_SCHEME) !== null) {
			return $relativeurl;
		}

		$parts = parse_url((string) $baseurl);

		// Chop off the query string in a base URL if it is there
		if (isset($parts['query'])) {
			$baseurl = strstr((string) $baseurl, '?', true);
		}

		// The rest is adapted from Puggan Se

		$minpartsinfinal = 3; // for everything except file:///
		if ($parts['scheme'] === 'file') {
			$minpartsinfinal = 4;
		}

		// logic for username:password@host:port ... query string etc. could go here too ... somewhere?

		$basepath = explode('/', (string) $baseurl); // will this handle correctly when query strings have '/'
		$relpath = explode('/', (string) $relativeurl);

		array_pop($basepath);

		$returnpath = array_merge($basepath, $relpath);
		$returnpath = array_reverse($returnpath);

		$parents = 0;
		foreach ($returnpath as $part_nr => $part_value) {
			/* if we find '..', remove this and the next element */
			if ($part_value == '..') {
				$parents++;
				unset($returnpath[$part_nr]);
			} /* if we find '.' remove this element */ elseif ($part_value == '.') {
				unset($returnpath[$part_nr]);
			} /* if this is a normal element, and we have unhandled '..', then remove this */ elseif (
				$parents > 0
			) {
				unset($returnpath[$part_nr]);
				$parents--;
			}
		}
		$returnpath = array_reverse($returnpath);
		if (count($returnpath) < $minpartsinfinal) {
			return false;
		}
		return implode('/', $returnpath);
	}

	// Base64url functies van https://www.php.net/manual/en/function.base64-encode.php#103849
	public static function base64url_encode($data)
	{
		return rtrim(strtr(base64_encode((string) $data), '+/', '-_'), '=');
	}

	public static function base64url_decode($data)
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
	public static function curl_request($url, $options = [])
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
