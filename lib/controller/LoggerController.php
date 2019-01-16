<?php

namespace CsrDelft\controller;

use CsrDelft\common\Ini;
use CsrDelft\controller\framework\AclController;
use Maknz\Slack\Client as SlackClient;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 16/01/2019
 */
class LoggerController extends AclController {

	/**
	 * Na hoe lang je weer een bericht mag loggen. Een half uur.
	 */
	const LOG_TIMEOUT = 1800;
	const LAATSTE_LOG_MELDING = 'laatste_log_melding';

	public function __construct($query) {
		parent::__construct($query, null);

		$this->acl = [
			'log' => 'P_LOGGED_IN' // Sta alleen leden toe om spam te voorkomen.
		];
	}

	public function performAction(array $args = array()) {
		$this->action = 'log';
		return parent::performAction($args);
	}

	public function POST_log() {
		if (!isset($_SESSION[self::LAATSTE_LOG_MELDING])) $_SESSION[self::LAATSTE_LOG_MELDING] = 0;

		if ($_SESSION[self::LAATSTE_LOG_MELDING] < time() - self::LOG_TIMEOUT && Ini::bestaat(Ini::SLACK)) {
			$message = $this->getPost('message');
			$col = $this->getPost('col');
			$line = $this->getPost('line');
			$url = $this->getPost('url');
			$error = $this->getPost('error');

			$slackConfig = Ini::lees(Ini::SLACK);
			$slackClient = new SlackClient($slackConfig['url'], $slackConfig);
			$foutmelding = $slackClient->createMessage();

			$moment = date('r');

			$foutmelding->setText(<<<MD
*Javascript Foutmelding*
```$message```
• Moment `$moment`
• Extra info `$error`
• Bestand `$url`
• Regel `$line`
• Kolom `$col`
• Url `{$_SERVER['REQUEST_URI']}`
• Method `{$_SERVER['REQUEST_METHOD']}`
• Veroorzaakt door `{$_SESSION['_uid']}`
• Browser `{$_SERVER['HTTP_USER_AGENT']}`
MD
			);

			$foutmelding->send();
		}

		$_SESSION[self::LAATSTE_LOG_MELDING] = time();

		$this->exit_http(204);
	}
}
