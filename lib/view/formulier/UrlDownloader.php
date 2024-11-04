<?php

namespace CsrDelft\view\formulier;

use CsrDelft\common\Util\FlashUtil;

/**
 * UrlDownloader.class.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 *
 * Download content van de gegeven url, gebruikt beschikbare mechanisme.
 */
class UrlDownloader
{
	/**
	 * Is er uberhaupt een methode beschikbaar
	 * @return bool
	 */
	public function isAvailable()
	{
		return $this->file_get_contents_available() or
			function_exists('curl_init') or
			function_exists('fsockopen');
	}

	/**
	 * Download file content met beschikbare middelen
	 *
	 * @param $url
	 * @return mixed|string
	 */
	public function file_get_contents($url)
	{
		if ($this->file_get_contents_available()) {
			return @file_get_contents($url);
		} else {
			return $this->curl_file_get_contents($url);
		}
	}

	/**
	 * Is file_get_contents() beschikbaar om van url te downloaden
	 *
	 * @return bool
	 */
	protected function file_get_contents_available()
	{
		return in_array(ini_get('allow_url_fopen'), ['On', 'Yes', 1]);
	}

	/**
	 * Download met behulp van cURL
	 * @param $url
	 * @return mixed
	 */
	protected function curl_file_get_contents($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		return curl_exec($ch);
	}
}
