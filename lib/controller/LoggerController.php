<?php

namespace CsrDelft\controller;

use CsrDelft\service\security\LoginService;
use Maknz\Slack\Client as SlackClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 16/01/2019
 */
class LoggerController {
	/**
	 * Na hoe lang je weer een bericht mag loggen. Een half uur.
	 */
	const LOG_TIMEOUT = 1800;
	const LAATSTE_LOG_MELDING = 'laatste_log_melding';

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/logger", methods={"GET", "POST"}, defaults={"_mag": "P_LOGGED_IN"})
	 */
	public function log(Request $request) {
		if (!isset($_SESSION[self::LAATSTE_LOG_MELDING])) $_SESSION[self::LAATSTE_LOG_MELDING] = 0;

		if ($_SESSION[self::LAATSTE_LOG_MELDING] < time() - self::LOG_TIMEOUT && !empty($_ENV['SLACK_URL'])) {
			$message = $request->request->get('message');
			$col = $request->request->get('col');
			$line = $request->request->get('line');
			$url = $request->request->get('url');
			$error = $request->request->get('error');
			$pagina = $request->request->get('pagina');

			$slackClient = new SlackClient($_ENV['SLACK_URL'], [
				'username' => $_ENV['SLACK_USERNAME'],
				'channel' => $_ENV['SLACK_CHANNEL'],
				'icon' => $_ENV['SLACK_ICON'],
			]);
			$foutmelding = $slackClient->createMessage();

			$moment = date('r');

			$foutmelding->setText(<<<MD
*Javascript Foutmelding*
```
$message
$error
```
• Moment `$moment`
• Bestand `$url`
• Positie `$line:$col`
• Pagina `$pagina`
• Veroorzaakt door `{$_SESSION[LoginService::SESS_UID]}`
• Browser `{$_SERVER['HTTP_USER_AGENT']}`
MD
			);

			$foutmelding->send();
		}

		$_SESSION[self::LAATSTE_LOG_MELDING] = time();

		return new Response("", 204);
	}
}
