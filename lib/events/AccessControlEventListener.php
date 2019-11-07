<?php

namespace CsrDelft\events;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\security\LoginModel;
use CsrDelft\service\CsrfService;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Controlleer access op route niveau.
 *
 * @package CsrDelft\events
 */
class AccessControlEventListener {
	const EXCLUDED_CONTROLLERS = [
		'CsrDelft\controller\ErrorController::handleException' => true,
		'twig.controller.exception:showAction' => true,
	];
	/**
	 * Controleer of gebruiker deze pagina mag zien.
	 *
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event) {
		if (!$event->getRequest()->get('_csrfUnsafe')) {
			CsrfService::preventCsrf();
		}

		if (isset(self::EXCLUDED_CONTROLLERS[$event->getRequest()->get('_controller')])){
			return;
		}

		$mag = $event->getRequest()->get('_mag');
		if (!$mag || !LoginModel::mag($mag)) {
			throw new CsrToegangException("Geen toegang");
		}
	}
}
