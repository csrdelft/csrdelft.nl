<?php

namespace CsrDelft\model\fiscaat\pin;

use CsrDelft\model\entity\fiscaat\pin\PinTransactie;
use DOMDocument;
use DOMXPath;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 22/02/2018
 */
class PinTransactieDownloader
{
	/**
	 * Post Field constants.
	 */
	const POST_FIELD_LOGIN_USERNAME = 'login.username';
	const POST_FIELD_LOGIN_PASSWORD = 'login.password';
	const POST_FIELD_PERIOD_FROM_DATE_DATE = 'period.from_date:date';
	const POST_FIELD_PERIOD_FROM_DATE_HOURS = 'period.from_date:hours';
	const POST_FIELD_PERIOD_FROM_DATE_MINUTES = 'period.from_date:minutes';
	const POST_FIELD_PERIOD_DURATION = 'period.duration';
	const POST_FIELD_STORE = 'select.store.container:select.store';

	/**
	 * Url constants.
	 */
	const RELATIVE_URL_LOGIN = '../nl/login/wicket:interface/:0:form::IFormSubmitListener::';
	const RELATIVE_URL_REPORT = '../nl/report';

	/**
	 * Date time constants.
	 */
	const DATETIME_FORMAT = 'Y-m-d H:i:s';
	const DATE_FORMAT_ONLINE = 'd-m-Y';
	const DATE_START_HOURS = '12';
	const DATE_START_MINUTES = '00';
	const DURATION_DAY = '0';

	public static function download(string $baseUrl, string $store, string $username, string $password, int $moment)
	{
		//1. Login
		$postFields = [
			self::POST_FIELD_LOGIN_USERNAME => $username,
			self::POST_FIELD_LOGIN_PASSWORD => $password,
		];
		$result = static::postPage(url2absolute($baseUrl, self::RELATIVE_URL_LOGIN), $postFields, null, true);

		//2. Parse session cookie from response
		$sessionCookie = static::parseSessionCookie($result);

		//3. GET report overview
		$result = static::getPage(url2absolute($baseUrl, self::RELATIVE_URL_REPORT), $sessionCookie);

		//4. Retrieve Merchant Transactions Url #article-content .report a[title=Merchant transactions]@href
		$xml = new DOMDocument();
		$xml->loadHTML($result);
		$xpath = new DOMXPath($xml);
		$merchantTransactionsUrl = $xpath->query('//a[@title = "Merchant transactions"]/@href')->item(0)->nodeValue;

		//5. GET Merchant Transactions Url
		$result = static::getPage(url2absolute($baseUrl, $merchantTransactionsUrl), $sessionCookie);

		//6. Retrieve Search Url: Only form tag -> action
		preg_match('/action="(.*?)"/', $result, $searchMatches);
		$searchUrl = $searchMatches[1];

		//7. POST Search with correct date
		$postFields = [
			self::POST_FIELD_PERIOD_FROM_DATE_DATE => date(self::DATE_FORMAT_ONLINE, strtotime($moment)),
			self::POST_FIELD_PERIOD_FROM_DATE_HOURS => self::DATE_START_HOURS,
			self::POST_FIELD_PERIOD_FROM_DATE_MINUTES => self::DATE_START_MINUTES,
			self::POST_FIELD_PERIOD_DURATION => self::DURATION_DAY,
			self::POST_FIELD_STORE => $store,
		];
		$result = self::postPage(url2absolute($baseUrl, $searchUrl), $postFields, $sessionCookie);

		//8. Parse html and create PinTransactie
		$xml = new DOMDocument();
		$xml->loadHTML($result);
		$xpath = new DOMXPath($xml);
		$tableRow = $xpath->query('//table[@class="table"]/tbody/tr');

		$pinTransacties = [];
		foreach ($tableRow as $row) {
			$labels = $xpath->query('td/label', $row);

			$pinTransactie = new PinTransactie();
			$pinTransactie->datetime = date(self::DATETIME_FORMAT, strtotime($labels->item(0)->nodeValue));
			$pinTransactie->brand = $labels->item(1)->nodeValue;
			$pinTransactie->merchant = $labels->item(2)->nodeValue;
			$pinTransactie->store = $labels->item(3)->nodeValue;
			$pinTransactie->terminal = $labels->item(4)->nodeValue;
			$pinTransactie->TID = $labels->item(5)->nodeValue;
			$pinTransactie->MID = $labels->item(6)->nodeValue;
			$pinTransactie->ref = $labels->item(7)->nodeValue;
			$pinTransactie->type = $labels->item(8)->nodeValue;
			$pinTransactie->amount = $labels->item(9)->nodeValue;
			$pinTransactie->AUTRSP = $labels->item(10)->nodeValue;
			$pinTransactie->STAN = $labels->item(11)->nodeValue;

			$pinTransactie->id = PinTransactieModel::instance()->create($pinTransactie);

			$pinTransacties[] = $pinTransactie;
		}

		return $pinTransacties;
	}

	/**
	 * Extract session cookie from headers string.
	 *
	 * @param string $headers
	 * @return string
	 */
	public static function parseSessionCookie($headers): string
	{
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
		$cookies = array();
		foreach ($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}

		$sessionCookie = 'JSESSIONID=' . $cookies['JSESSIONID'];
		return $sessionCookie;
	}

	/**
	 * @param string $url
	 * @param string $sessionCookie
	 * @return string
	 */
	public static function getPage($url, $sessionCookie): string
	{
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_COOKIE, $sessionCookie);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl_handle);

		return $result;
	}

	/**
	 * @param string $url
	 * @param string[] $postFields
	 * @param string $sessionCookie
	 * @param bool $returnHeader
	 * @return string
	 */
	public static function postPage($url, $postFields, $sessionCookie, $returnHeader = false): string
	{
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_POST, true);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($postFields));
		curl_setopt($curl_handle, CURLOPT_COOKIE, $sessionCookie);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl_handle, CURLOPT_HEADER, $returnHeader);
		$result = curl_exec($curl_handle);

		return $result;
	}
}
