<?php
/**
 * index.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Entry point voor stek modules.
 */

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\Controller;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\TimerModel;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\CsrLayoutPage;

require_once 'configuratie.include.php';

// start MVC
$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);

if (empty($class)) {
    $class = 'CmsPagina';
}
// toegang tot leden website dicht-timmeren:
switch ($class) {
    // toegestaan voor iedereen:
    case 'Login':
    case 'CmsPagina':
    case 'Forum':
    case 'FotoAlbum':
    case 'Agenda':
    case 'Mededelingen':
		case 'ContactFormulier':
        break;

    // de rest alleen voor ingelogde gebruikers:
    default:
        if (!LoginModel::mag('P_LOGGED_IN')) {
			redirect_via_login(REQUEST_URI);
        }
}

$namespacedClassName = 'CsrDelft\\controller\\' . $class . 'Controller';

if (class_exists($namespacedClassName)) {
	/** @var Controller $controller */
	$controller = new $namespacedClassName(REQUEST_URI);
} else {
	http_response_code(404);
	exit;
}

$view = null;
try {
	$controller->performAction();
	$view = $controller->getView();
} catch (CsrGebruikerException $exception) {
	http_response_code(400);
	echo $exception->getMessage();
	exit;
} catch (CsrToegangException $exception) {
	http_response_code($exception->getCode());
	if ($controller->getMethod() == 'POST') {
		die($exception->getMessage());
	} // Redirect to login form
	elseif (LoginModel::getUid() === 'x999') {
		redirect_via_login(REQUEST_URI);
	}
    // GUI 403
    /** @var CsrDelft\model\entity\CmsPagina $errorpage */
    $errorpage = CmsPaginaModel::get($exception->getCode());
	$body = new CmsPaginaView($errorpage);
	$view = new CsrLayoutPage($body);
}


if (DB_CHECK AND LoginModel::mag('P_ADMIN')) {

    $queries = DatabaseAdmin::instance()->getQueries();
    if (!empty($queries)) {
        if (DB_MODIFY) {
            header('Content-Type: text/x-sql');
            header('Content-Disposition: attachment;filename=DB_modify_' . time() . '.sql');
            foreach ($queries as $query) {
                echo $query . ";\n";
            }
            exit;
        } else {
            debugprint($queries);
        }
    }
}

if (TIME_MEASURE) {
    TimerModel::instance()->time();
}

$view->view();
// einde MVC
