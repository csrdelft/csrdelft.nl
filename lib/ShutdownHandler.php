<?php

namespace CsrDelft;

use CsrDelft\model\DebugLogModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\TimerModel;
use Maknz\Slack\Client as SlackClient;
use Exception;

/**
 * Class ShutdownHandler.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
final class ShutdownHandler
{
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
     * Runt in Productie mode.
     */
    public static function slackHandler() {
        $debug = self::getDebug();
        if ($debug !== null && file_exists(ETC_PATH . 'slack.ini')) {
            $slackConfig = parse_ini_file(ETC_PATH . 'slack.ini');
            $slackClient = new SlackClient($slackConfig['url'], $slackConfig);
            $foutmelding = $slackClient->createMessage();

            $errorName = \CsrDelft\errorName($debug['error']['type']);
            $moment = date('r');

            $foutmelding->setText(<<<MD
*Foutmelding `{$debug['error']['message']}`*
• Moment `$moment`
• Type `$errorName`
• Bestand `{$debug['error']['file']}`
• Regel `{$debug['error']['line']}`
• Url `{$debug['SERVER']['REQUEST_URI']}`
• Veroorzaakt door `{$debug['SESSION']['_uid']}`
• Browser `{$debug['SERVER']['HTTP_USER_AGENT']}`
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
        if ($exception instanceof Exception) {
            if ((defined('DEBUG') && DEBUG) || LoginModel::mag('P_LOGGED_IN')) {
                echo str_replace('#', '<br />#', $exception); // stacktrace
                printDebug();
            }
        }
    }

    /**
     * @return array|null
     */
    private static function getDebug()
    {
        $error = error_get_last();
        if ($error !== null) {
            $debug['error'] = $error;
            $debug['trace'] = debug_backtrace();
            $debug['POST'] = $_POST;
            $debug['GET'] = $_GET;
            $debug['SESSION'] = isset($_SESSION) ? $_SESSION : MODE;
            $debug['SERVER'] = $_SERVER;
            return $debug;
        }
        return null;
    }

    /**
     * @param $debug
     *
     * @return bool
     */
    private static function isError($debug)
    {
        return $debug['error']['type'] === E_CORE_ERROR || $debug['error']['type'] === E_ERROR;
    }
}
