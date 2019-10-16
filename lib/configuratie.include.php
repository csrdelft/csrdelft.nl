<?php
/**
 * configuratie.include.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * First include for entire application.
 * Handle exceptions gracefully and notify admin.
 * Configure sessions.
 * Boot framework.
 */
require_once 'defines.defaults.php';
if (ONDERHOUD) {
	http_response_code(503);
	echo <<<'HTML'
<!doctype html>
<html lang=nl>
<title>C.S.R. Delft - Onderhoud</title>
<meta charset=utf-8>
<meta name=viewport content="width=device-width, initial-scale=1.0">

<body style="font-family: sans-serif; text-align: center;">
<h1>Onderhoud</h1>
<p>De website is momenteel in onderhoud. Dit duurt meestal niet lang.</p>
<img alt="Beeldmerk van de Vereniging" src="/dist/images/beeldmerk.png">
HTML;
	exit;
}

use CsrDelft\common\Ini;
use CsrDelft\common\ShutdownHandler;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\instellingen\InstellingenModel;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\LogModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;

// Zet omgeving klaar.
require_once __DIR__ . '/../config/bootstrap.php';

// Registreer foutmelding handlers
if (DEBUG) {
	register_shutdown_function([ShutdownHandler::class, 'debugLogHandler']);
} else {
	register_shutdown_function([ShutdownHandler::class, 'emailHandler']);
	set_error_handler([ShutdownHandler::class, 'slackHandler']);
	register_shutdown_function([ShutdownHandler::class, 'slackShutdownHandler']);
	register_shutdown_function([ShutdownHandler::class, 'errorPageHandler']);
}

register_shutdown_function([ShutdownHandler::class, 'timerHandler']);
register_shutdown_function([ShutdownHandler::class, 'touchHandler']);

// alle meldingen tonen
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');

// datum weergave enzo
setlocale(LC_ALL, 'nl_NL.utf8');
setlocale(LC_ALL, 'nld_nld');
date_default_timezone_set('Europe/Amsterdam');


// default is website mode
if (getenv('CI')) {
	define('MODE', 'TRAVIS');
} elseif (php_sapi_name() === 'cli') {
	define('MODE', 'CLI');
} else {
	define('MODE', 'WEB');
}

if (isset($_SERVER['REQUEST_URI'])) {
	$req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
} else {
	$req = null;
}
define('REQUEST_URI', $req);

if (isset($_SERVER['HTTP_REFERER'])) {
	$ref = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
} else {
	$ref = null;
}
define('HTTP_REFERER', $ref);


// Use HTTP Strict Transport Security to force client to use secure connections only
if (FORCE_HTTPS) {
	if (!(isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && $_SERVER['HTTP_X_FORWARDED_SCHEME'] === 'https') && MODE !== 'CLI' && MODE !== 'TRAVIS') {
		// check if the private token has been send over HTTP
		$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
		if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
			$account = AccountModel::instance()->find('private_token = ?', array($token), null, null, 1)->fetch();
			// Reset private token, user has to get a new one
			AccountModel::instance()->resetPrivateToken($account);
			// TODO: Log dit
		}
		// redirect to https
		header('Location: ' . CSR_ROOT . REQUEST_URI, true, 301);
		// we are in cleartext at the moment, prevent further execution and output
		die();
	}
}

$cred = Ini::lees(Ini::MYSQL);
if ($cred === false) {
	$cred = array(
		'host' => 'localhost',
		'user' => 'admin',
		'pass' => 'password',
		'db' => 'csrdelft'
	);
}

CsrDelft\Orm\Configuration::load(array(
	'cache_path' => DATA_PATH,
	'db' => $cred
));

// Router
switch (constant('MODE')) {
	case 'TRAVIS':
		if (isSyrinx()) die("Syrinx is geen Travis!");
		break;
	case 'CLI':
		//require_once 'model/security/CliLoginModel.class.php';
		// Late static binding requires explicitly
		// calling instance() before any static method!
		LoginModel::instance();
		if (!LoginModel::mag(P_ADMIN)) {
			die('access denied');
		}
		break;

	case 'WEB':
		InstellingenModel::instance()->prefetch();

		// Terugvinden van temp upload files
		ini_set('upload_tmp_dir', TMP_PATH);

		// Sessie configureren
		ini_set('session.name', 'CSRSESSID');
		ini_set('session.save_path', SESSION_PATH);
		ini_set('session.hash_function', 'sha512');
		ini_set('session.cache_limiter', 'nocache');
		ini_set('session.use_trans_sid', 0);
		// Sync lifetime of FS based PHP session with DB based C.S.R. session
		ini_set('session.gc_maxlifetime', (int)instelling('beveiliging', 'session_lifetime_seconds'));
		ini_set('session.use_strict_mode', true);
		ini_set('session.use_cookies', true);
		ini_set('session.use_only_cookies', true);
		ini_set('session.cookie_lifetime', 0);
		ini_set('session.cookie_path', '/');
		ini_set('session.cookie_domain', CSR_DOMAIN);
		ini_set('session.cookie_secure', FORCE_HTTPS);
		ini_set('session.cookie_httponly', true);
		ini_set('xdebug.max_nesting_level', 2000);
		session_set_cookie_params(0, '/', CSR_DOMAIN, FORCE_HTTPS, true);

		session_start();
		if (session_id() == 'deleted') {
			// Deletes old session
			session_regenerate_id(true);
		}
		// Validate login
		LoginModel::instance();

		LogModel::instance()->log();

		// Prefetch
		LidInstellingenModel::instance()->prefetch('uid = ?', array(LoginModel::getUid()));
		VerticalenModel::instance()->prefetch();
		ForumModel::instance()->prefetch();

		// Database modus meldingen
		if (DB_MODIFY OR DB_DROP) {
			if (DEBUG) {
				if (DB_DROP) {
					setMelding('DB_DROP enabled', 2);
				}
			} elseif (!LoginModel::mag(P_ADMIN)) {
				redirect('/onderhoud.html');
			} elseif (DB_DROP) {
				setMelding('DB_DROP enabled', 2);
			} elseif (DB_MODIFY) {
				setMelding('DB_MODIFY enabled', 2);
			}
		}
		break;

	default:
		die('configuratie.include.php unsupported MODE: ' . MODE);
}
