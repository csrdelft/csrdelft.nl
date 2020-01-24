<?php

namespace CsrDelft\events;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\security\LoginModel;
use CsrDelft\service\CsrfService;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Controlleer access op route niveau.
 *
 * @package CsrDelft\events
 */
class AccessControlEventListener {
	const EXCLUDED_CONTROLLERS = [
		'CsrDelft\controller\ErrorController::handleException' => true,
		'twig.controller.exception::showAction' => true,
	];

	/**
	 * Controleer of gebruiker deze pagina mag zien.
	 *
	 * @param ControllerEvent $event
	 */
	public function onKernelController(ControllerEvent $event) {
		if (!$event->getRequest()->get('_csrfUnsafe')) {
			CsrfService::preventCsrf();
		}

		$controller = $event->getRequest()->get('_controller');

		if (isset(self::EXCLUDED_CONTROLLERS[$controller])){
			return;
		}

		$mag = $event->getRequest()->get('_mag');
		if (!$mag || !LoginModel::mag($mag)) {
			if (DEBUG) {
				throw new CsrToegangException("Geen toegang tot " . $controller . ", ten minste " . $mag . " nodig.");
			} else {
				throw new CsrToegangException("Geen toegang");
			}
		}
	}
}
