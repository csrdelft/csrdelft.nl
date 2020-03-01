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

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Ini;
use CsrDelft\common\ShutdownHandler;
use CsrDelft\Kernel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\CliLoginModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\DependencyManager;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use CsrDelft\Orm\Persistence\OrmMemcache;
use CsrDelft\repository\LogRepository;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/lib/defines.defaults.php';
// Zet omgeving klaar.
require __DIR__ . '/../config/bootstrap.php';

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

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
	Request::setTrustedProxies(
		explode(',', $trustedProxies),
		Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST
	);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
	Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$cred = Ini::lees(Ini::MYSQL);
if ($cred === false) {
	$cred = array(
		'host' => 'localhost',
		'user' => 'admin',
		'pass' => 'password',
		'db' => 'csrdelft'
	);
}

$pdo = new PDO('mysql:host=' . $cred['host'] . ';dbname=' . $cred['db'], $cred['user'], $cred['pass'], [
	PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8MB4'",
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Set csrdelft/orm parts of the container
$container->set(OrmMemcache::class, new OrmMemcache(MEMCACHED_PATH));
$container->set(Database::class, new Database($pdo));
$container->set(DatabaseAdmin::class, new DatabaseAdmin($pdo));

DependencyManager::setContainer($container);
ContainerFacade::init($container);

// ---
// Vanaf hier is Symfony geinitialiseerd.
// ---

// Use HTTP Strict Transport Security to force client to use secure connections only
if (FORCE_HTTPS) {
	if (!(isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && $_SERVER['HTTP_X_FORWARDED_SCHEME'] === 'https') && MODE !== 'CLI' && MODE !== 'TRAVIS') {
		// check if the private token has been send over HTTP
		$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
		if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
			$account = $container->get(AccountModel::class)->find('private_token = ?', array($token), null, null, 1)->fetch();
			// Reset private token, user has to get a new one
			$container->get(AccountModel::class)->resetPrivateToken($account);
			// TODO: Log dit
		}
		// redirect to https
		header('Location: ' . CSR_ROOT . REQUEST_URI, true, 301);
		// we are in cleartext at the moment, prevent further execution and output
		die();
	}
}

// Router
switch (constant('MODE')) {
	case 'TRAVIS':
		if (isSyrinx()) die("Syrinx is geen Travis!");
		break;
	case 'CLI':
		// Override LoginModel in container to use the Cli version
		$cliLoginModel = $container->get(CliLoginModel::class);
		$container->set(LoginModel::class, $cliLoginModel);

		$cliLoginModel->authenticate();

		if (!$cliLoginModel::mag(P_ADMIN)) {
			die('access denied');
		}
		break;

	case 'WEB':
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
		ini_set('log_errors_max_len', 0);
		ini_set('xdebug.max_nesting_level', 2000);
		session_set_cookie_params(0, '/', CSR_DOMAIN, FORCE_HTTPS, true);

		session_start();
		if (session_id() == 'deleted') {
			// Deletes old session
			session_regenerate_id(true);
		}
		// Validate login
		$container->get(LoginModel::class)->authenticate();

		$container->get(LogRepository::class)->log();

		// Prefetch
		$container->get(LidInstellingenModel::class)->prefetch('uid = ?', [LoginModel::getUid()]);
		$container->get(VerticalenModel::class)->prefetch();
		$container->get(ForumModel::class)->prefetch();

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

// ---
// Nu heeft de gebruiker een sessie en kan er echt begonnen worden.
// ---

return $kernel;
