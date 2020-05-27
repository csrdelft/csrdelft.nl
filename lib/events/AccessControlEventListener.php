<?php

namespace CsrDelft\events;

use CsrDelft\common\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\service\CsrfService;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Controlleer access op route niveau.
 *
 * @package CsrDelft\events
 */
class AccessControlEventListener {
	const EXCLUDED_CONTROLLERS = [
		'error_controller' => true,
		'CsrDelft\controller\ErrorController::handleException' => true,
		'twig.controller.exception::showAction' => true,
	];
	/**
	 * @var CsrfService
	 */
	private $csrfService;
	/**
	 * @var Reader
	 */
	private $annotations;

	public function __construct(CsrfService $csrfService, Reader $annotations) {
		$this->csrfService = $csrfService;
		$this->annotations = $annotations;
	}

	/**
	 * Controleer of gebruiker deze pagina mag zien.
	 *
	 * @param ControllerEvent $event
	 * @param CsrfService $csrfService
	 */
	public function onKernelController(ControllerEvent $event) {
		if (!$event->getRequest()->get('_csrfUnsafe')) {
			$this->csrfService->preventCsrf();
		}

		$controller = $event->getRequest()->get('_controller');

		if (isset(self::EXCLUDED_CONTROLLERS[$controller])){
			return;
		}


		$reflectionMethod = new \ReflectionMethod($event->getController()[0], $event->getController()[1]);

		/** @var Auth $authAnnotation */
		$authAnnotation = $this->annotations->getMethodAnnotation($reflectionMethod, Auth::class);

		if ($authAnnotation) {
			$mag = $authAnnotation->getMag();
		} else {
			$mag = $event->getRequest()->get('_mag');
		}

		if (!$mag) {
			throw new CsrException("Route heeft geen @Auth: " . $controller);
		}

		if (!LoginService::mag($mag)) {
			if (DEBUG) {
				throw new CsrToegangException("Geen toegang tot " . $controller . ", ten minste " . $mag . " nodig.");
			} else {
				throw new CsrToegangException("Geen toegang");
			}
		}
	}
}
