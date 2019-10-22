<?php

namespace CsrDelft\events;

use CsrDelft\controller\GeenToegangController;
use CsrDelft\model\security\LoginModel;
use CsrDelft\service\CsrfService;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Controlleer access op route niveau.
 *
 * @package CsrDelft\events
 */
class AccessControlEventListener {
	/**
	 * Controleer of gebruiker deze pagina mag zien.
	 *
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event) {
		if (!$event->getRequest()->get('_csrfUnsafe')) {
			CsrfService::preventCsrf();
		}
		$mag = $event->getRequest()->get('_mag');
		if (!$mag || !LoginModel::mag($mag)) {
			$event->setController([GeenToegangController::class, 'fout_403']);
		}
	}
}
