<?php

namespace CsrDelft\common;

use Maknz\Slack\Client as SlackClient;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Throwable;

/**
 * Class ShutdownHandler.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
final class ShutdownHandler {
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

			$dumper = new HtmlDumper();
			$dumper->setTheme('light');
			$cloner = new VarCloner();

			mail('pubcie@csrdelft.nl', $subject, $dumper->dump($cloner->cloneVar($debug), true), implode("\r\n", $headers));
		}
	}

	public static function emailException(Throwable $exception) {
		$debug['type'] = get_class($exception);
		$debug['message'] = $exception->getMessage();
		$debug['trace'] = $exception->getTrace();
		$debug['POST'] = $_POST;
		$debug['GET'] = $_GET;
		$debug['SESSION'] = isset($_SESSION) ? $_SESSION : null;
		$debug['SERVER'] = $_SERVER;
		unset($debug['SERVER']['HTTP_COOKIE']); // Voorkom dat sessie en remember cookies gemaild worden
		unset($debug['SERVER']['DATABASE_URL']);

		$headers[] = 'From: Fatal error handler <pubcie@csrdelft.nl>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
		$subject = 'Fatal error: ' . $debug['message'];
		$dumper = new HtmlDumper();
		$dumper->setTheme('light');
		$cloner = new VarCloner();

		mail('pubcie@csrdelft.nl', $subject, $dumper->dump($cloner->cloneVar($debug), true), implode("\r\n", $headers));
	}

	public static function slackException(Throwable $exception) {
		static::slackHandler(1, $exception->getMessage(), $exception->getFile(), $exception->getLine());
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
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 */
	public static function slackHandler($errno, $errstr, $errfile, $errline) {
		ShutdownHandler::triggerSlackMessage($errno, $errstr, $errfile, $errline, false);
	}

	/**
	 * Stuur een schop naar de PubCie Slack.
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param bool $force
	 */
	public static function triggerSlackMessage($errno, $errstr, $errfile, $errline, $force=false) {
		if (!$force && !(error_reporting() & $errno)) {
			// Deze error is gesuppressed.
			return;
		}


		$debug = self::getDebug();
		if ($debug !== null && !empty($_ENV['SLACK_URL'])) {
			$slackClient = new SlackClient($_ENV['SLACK_URL'], [
				'username' => $_ENV['SLACK_USERNAME'],
				'channel' => $_ENV['SLACK_CHANNEL'],
				'icon' => $_ENV['SLACK_ICON'],
			]);
			$foutmelding = $slackClient->createMessage();

			$errorName = errorName($errno);
			$moment = date('r');
			$commit = commitHash();
			$commitLink = commitLink();

			$foutmelding->setText(<<<MD
*Foutmelding*
```{$errstr}```
• Moment `$moment`
• Type `$errorName`
• Bestand `{$errfile}`
• Regel `{$errline}`
• Url `{$_SERVER['REQUEST_URI']}`
• Method `{$_SERVER['REQUEST_METHOD']}`
• Veroorzaakt door <https://csrdelft.nl/profiel/{$_SESSION['_uid']}|`{$_SESSION['_uid']}`>
• Browser `{$_SERVER['HTTP_USER_AGENT']}`
• Commit <$commitLink|`$commit`>
MD
			);

			$foutmelding->send();
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
			$debug['SESSION'] = isset($_SESSION) ? $_SESSION : null;
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
