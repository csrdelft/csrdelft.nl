<?php

namespace CsrDelft;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\Controller;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\ToResponse;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * De LegacyRouter kiest een controller op basis van $_GET['c'], in htdocs/.htaccess
 * wordt de waarde van deze variabele gezet op basis van de REQUEST_URI.
 *
 * @deprecated Vervangen door de Symfony router
 */
class LegacyRouter {
	public static function route(): ToResponse {
		// Alle legacy routes zijn intern
		if (!LoginModel::mag(P_LOGGED_IN)) {
			redirect_via_login(REQUEST_URI);
		}

		try {
			$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);

			if (empty($class)) {
				throw new CsrToegangException();
			}

			$class = 'CsrDelft\\controller\\' . $class . 'Controller';

			if (!class_exists($class)) {
				throw new CsrToegangException();
			}

			/** @var Controller $controller */
			$controller = new $class(REQUEST_URI);

			$controller->performAction();
			return $controller->getView();
		} catch (ResourceNotFoundException $exception) {
			http_response_code(404);
			return view('fout.404');
		} catch (MethodNotAllowedException $exception) {
			http_response_code(404);
			return view('fout.404');
		} catch (CsrGebruikerException $exception) {
			http_response_code(400);
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				die($exception->getMessage());
			}
			return view('fout.400', ['bericht' => $exception->getMessage()]);
		} catch (CsrToegangException $exception) {
			http_response_code($exception->getCode());
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				die($exception->getMessage());
			} // Redirect to login form
			elseif (LoginModel::getUid() === 'x999') {
				redirect_via_login(REQUEST_URI);
			}

			switch ($exception->getCode()) {
				case 404: return view('fout.404');
				case 403: return view('fout.403');
				case 400: return view('fout.400', ['bericht' => $exception->getMessage()]);
				default: return view('fout.500');
			}
		}
	}
}
