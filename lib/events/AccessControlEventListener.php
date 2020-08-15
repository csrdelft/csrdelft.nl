<?php

namespace CsrDelft\events;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\service\CsrfService;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionMethod;
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
		'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction' => true,
	];
	/**
	 * @var CsrfService
	 */
	private $csrfService;
	/**
	 * @var Reader
	 */
	private $annotations;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(CsrfService $csrfService, Reader $annotations, EntityManagerInterface $entityManager) {
		$this->csrfService = $csrfService;
		$this->annotations = $annotations;
		$this->em = $entityManager;
	}

	/**
	 * Controleer of gebruiker deze pagina mag zien.
	 *
	 * @param ControllerEvent $event
	 * @throws \ReflectionException
	 */
	public function onKernelController(ControllerEvent $event) {
		$controller = $event->getRequest()->get('_controller');
		$reflectionMethod = new ReflectionMethod($event->getController()[0], $event->getController()[1]);
		/** @var CsrfUnsafe $authAnnotation */
		$csrfUnsafeAnnotation = $this->annotations->getMethodAnnotation($reflectionMethod, CsrfUnsafe::class);

		if (!$event->getRequest()->get('_csrfUnsafe') && $csrfUnsafeAnnotation === null) {
			$this->csrfService->preventCsrf();
		}

		if (isset(self::EXCLUDED_CONTROLLERS[$controller])){
			return;
		}

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

		if (LoginService::mag('commissie:NovCie') && $this->em->getFilters()->isEnabled('verbergNovieten')) {
			$this->em->getFilters()->disable('verbergNovieten');
		}
	}
}
