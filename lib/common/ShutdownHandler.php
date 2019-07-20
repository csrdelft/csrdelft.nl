<?php

namespace CsrDelft\common;

use CsrDelft\model\DebugLogModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\TimerModel;
use Maknz\Slack\Client as SlackClient;

/**
 * Class ShutdownHandler.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
final class ShutdownHandler {
	/**
	 * Zet de http status code. Voorkomt dat stacktraces weergegeven worden.
	 *
	 * Runt in Productie mode.
	 */
	public static function errorPageHandler() {
		$debug = self::getDebug();
		if ($debug !== null && self::isError($debug)) {
			http_response_code(500);
			view('fout.500')->view();
		}
	}

	/**
	 * Stuur een mail naar de PubCie.
	 *
	 * Runt in Productie mode.
	 */
	public static function emailHandler() {
		$debug = self::getDebug();
		if ($debug !== null && self::isError($debug)) {
			$headers[] = 'From: Fatal error handler <pubcie@csrdelft.nl>';
			$headers[] = 'Content-Type: text/plain; charset=UTF-8';
			$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
			$subject = 'Fatal error: ' . $debug['error']['message'];
			mail('pubcie@csrdelft.nl', $subject, print_r($debug, true), implode("\r\n", $headers));
		}
	}

	/**
	 * Schrijf naar de debug log in de database.
	 *
	 * Runt in Debug mode.
	 */
	public static function debugLogHandler() {
		$debug = static::getDebug();
		if ($debug !== null) {
			DebugLogModel::instance()->log(__FILE__, 'fatal_handler', func_get_args(), print_r($debug, true));
		}
	}

	/**
	 * Raak het 'laaste foutmelding' bestand aan.
	 *
	 * Runt in Debug en Productie mode.
	 */
	public static function touchHandler() {
		$debug = self::getDebug();
		if ($debug !== null && self::isError($debug)) {
			touch(DATA_PATH . 'foutmelding.last');
		}
	}

	/**
	 * Stuur een schop naar de PubCie Slack.
	 *
	 * Wordt uitgevoerd bij E_ERROR en E_CORE_ERROR.
	 *
	 * Runt in Productie mode.
	 */
	public static function slackShutdownHandler() {
		$debug = self::getDebug();
		if ($debug !== null && self::isError($debug)) {
			$errno = $debug['error']['type'];
			$errstr = $debug['error']['message'];
			$errfile = $debug['error']['file'];
			$errline = $debug['error']['line'];
			static::slackHandler($errno, $errstr, $errfile, $errline);
		}
	}

	/**
	 * Stuur een schop naar de PubCie Slack.
	 *
	 * Is een `error_handler` en wordt niet uitgevoerd bij E_ERROR.
	 *
	 * Runt in Productie mode.
	 */
	public static function slackHandler($errno, $errstr, $errfile, $errline) {
		if (!(error_reporting() & $errno)) {
			// Deze error is gesuppressed.
			return;
		}


		$debug = self::getDebug();
		if ($debug !== null
			&& Ini::bestaat(Ini::SLACK)
		) {
			$slackConfig = Ini::lees(Ini::SLACK);
			$slackClient = new SlackClient($slackConfig['url'], $slackConfig);
			$foutmelding = $slackClient->createMessage();

			$errorName = errorName($errno);
			$moment = date('r');

			$foutmelding->setText(<<<MD
*Foutmelding*
```{$errstr}```
• Moment `$moment`
• Type `$errorName`
• Bestand `{$errfile}`
• Regel `{$errline}`
• Url `{$_SERVER['REQUEST_URI']}`
• Method `{$_SERVER['REQUEST_METHOD']}`
• Veroorzaakt door `{$_SESSION['_uid']}`
• Browser `{$_SERVER['HTTP_USER_AGENT']}`
MD
			);

			$foutmelding->send();
		}
	}

	/**
	 * Time de request als dat nodig is.
	 *
	 * Runt in Debug en Productie mode.
	 */
	public static function timerHandler() {
		if (defined('TIME_MEASURE') && TIME_MEASURE) {
			TimerModel::instance()->log();
		}
	}

	/**
	 * Print de stacktrace als dat mag.
	 *
	 * Runt in Debug en Productie mode.
	 *
	 * @param null $exception
	 */
	public static function stacktraceHandler($exception = null) {
		if ($exception instanceof \Exception) {
			if ((defined('DEBUG') && DEBUG) || LoginModel::mag(P_LOGGED_IN)) {
				echo str_replace('#', '<br />#', $exception); // stacktrace
				printDebug();
			}
		}
	}

	/**
	 * @return array|null
	 */
	private static function getDebug() {
		$error = error_get_last();
		if ($error !== null) {
			// Voorkom dump van mysql wachtwoord als de database eruit ligt
			$error['message'] = preg_replace('/PDO->__construct\(.*/', 'PDO->__construct(...)', $error['message']);
			$debug['error'] = $error;
			$debug['trace'] = debug_backtrace();
			$debug['POST'] = $_POST;
			$debug['GET'] = $_GET;
			$debug['SESSION'] = isset($_SESSION) ? $_SESSION : MODE;
			$debug['SERVER'] = $_SERVER;
			unset($debug['SERVER']['HTTP_COOKIE']); // Voorkom dat sessie en remember cookies gemaild worden
			return $debug;
		}
		return null;
	}

	/**
	 * @param $debug
	 *
	 * @return bool
	 */
	private static function isError($debug) {
		return $debug['error']['type'] === E_CORE_ERROR || $debug['error']['type'] === E_ERROR;
	}
}
