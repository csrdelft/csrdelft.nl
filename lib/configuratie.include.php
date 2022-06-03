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
use CsrDelft\common\ShutdownHandler;
use CsrDelft\Kernel;
use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

// Zet omgeving klaar.
require __DIR__ . '/../config/bootstrap.php';

// Registreer foutmelding handlers
if (!isCi() && !isCli()) {
    if (DEBUG) {
        umask(0000);

        Debug::enable();
    } else {
        register_shutdown_function([ShutdownHandler::class, 'emailHandler']);
        set_error_handler([ShutdownHandler::class, 'slackHandler']);
        register_shutdown_function([ShutdownHandler::class, 'slackShutdownHandler']);
    }
}

// alle meldingen tonen
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');

// datum weergave enzo
setlocale(LC_ALL, 'nl_NL');
//setlocale(LC_ALL, 'nl_NL.utf8');
setlocale(LC_ALL, 'nld_nld');
date_default_timezone_set('Europe/Amsterdam');

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(
        explode(',', $trustedProxies),
        Request::HEADER_X_FORWARDED_ALL
    );
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

ContainerFacade::init($container);

// ---
// Vanaf hier is Symfony geinitialiseerd.
// ---

// Use HTTP Strict Transport Security to force client to use secure connections only
if (FORCE_HTTPS) {
    if (!(isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && $_SERVER['HTTP_X_FORWARDED_SCHEME'] === 'https') && !isCi() && !isCli()) {
        // check if the private token has been send over HTTP
        $token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
        if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
            if ($account = $container->get(AccountRepository::class)->findOneBy(['private_token' => $token])) {
                // Reset private token, user has to get a new one
                $container->get(AccountRepository::class)->resetPrivateToken($account);
            }
            // TODO: Log dit
        }
        // redirect to https
        header('Location: ' . CSR_ROOT . $_SERVER['REQUEST_URI'], true, 301);
        // we are in cleartext at the moment, prevent further execution and output
        die();
    }
}

if (isCi() && isSyrinx()) die("Syrinx is geen Travis!");

if (!isCli()) {
    // Sessie configureren
    ini_set('session.name', 'CSRSESSID');
    ini_set('session.save_path', SESSION_PATH);
    ini_set('session.use_trans_sid', 0);
    // Sync lifetime of FS based PHP session with DB based C.S.R. session
    ini_set('session.gc_maxlifetime', (int)instelling('beveiliging', 'session_lifetime_seconds'));
    ini_set('session.use_strict_mode', true);
    ini_set('session.use_cookies', true);
    ini_set('session.use_only_cookies', true);
    ini_set('session.cookie_secure', FORCE_HTTPS);
    ini_set('session.cookie_httponly', true);
    ini_set('log_errors_max_len', 0);
    ini_set('xdebug.max_nesting_level', 2000);
    ini_set('intl.default_locale', 'nl');
    session_set_cookie_params(0, '/', '', FORCE_HTTPS, true);
}

return $kernel;
