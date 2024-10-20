<?php

namespace CsrDelft\events;

use ReflectionException;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\CsrException;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\service\CsrfService;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Controlleer access op route niveau.
 *
 * @package CsrDelft\events
 */
class AccessControlEventListener
{
	const EXCLUDED_CONTROLLERS = [
		'error_controller' => true,
		'CsrDelft\controller\ErrorController::handleException' => true,
		'twig.controller.exception::showAction' => true,
		'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction' => true,
		'league.oauth2_server.controller.token::indexAction' => true,
		'league.oauth2_server.controller.authorization::indexAction' => true,
	];

	public function __construct(
		private readonly CsrfService $csrfService,
		private readonly Security $security,
		private readonly Reader $annotations,
		private readonly EntityManagerInterface $em
	) {
	}

	/**
	 * Controleer of gebruiker deze pagina mag zien.
	 *
	 * @param ControllerEvent $event
	 * @throws ReflectionException
	 */
	public function onKernelController(ControllerEvent $event)
	{
		$request = $event->getRequest();

		if (!$event->isMainRequest()) {
			return;
		}

		$reflectionMethod = ReflectionUtil::createReflectionMethod(
			$event->getController()
		);

		$csrfUnsafeAttribute = $request->attributes->get('_csrfUnsafe');
		/** @var CsrfUnsafe $authAnnotation */
		$csrfUnsafeAnnotation = $this->annotations->getMethodAnnotation(
			$reflectionMethod,
			CsrfUnsafe::class
		);

		$isInApi =
			str_starts_with($request->getPathInfo(), '/API/2.0') ||
			str_starts_with($request->getPathInfo(), '/api/v3/');

		if (
			$isInApi === false &&
			$csrfUnsafeAttribute === null &&
			$csrfUnsafeAnnotation === null &&
			!$this->csrfService->preventCsrf($request)
		) {
			throw new AccessDeniedException('Ongeldige CSRF token');
		}

		$controller = $request->attributes->get('_controller');
		if (isset(self::EXCLUDED_CONTROLLERS[$controller])) {
			return;
		}

		if (
			$this->annotations->getMethodAnnotation(
				$reflectionMethod,
				IsGranted::class
			)
		) {
			return;
		}

		/** @var Auth $authAnnotation */
		$authAnnotation = $this->annotations->getMethodAnnotation(
			$reflectionMethod,
			Auth::class
		);

		$mag = $authAnnotation
			? $authAnnotation->getMag()
			: $request->attributes->get('_mag');

		if (!$mag) {
			throw new CsrException('Route heeft geen @Auth: ' . $controller);
		}

		$user = $this->security->getUser();

		if ($user && $user->blocked_reason) {
			throw new NotFoundHttpException('Geblokkeerd: ' . $user->blocked_reason);
		}

		if (!$this->security->isGranted($mag)) {
			if (DEBUG) {
				throw new AccessDeniedException(
					'Geen toegang tot ' . $controller . ', ten minste ' . $mag . ' nodig.'
				);
			} else {
				throw new AccessDeniedException('Geen toegang');
			}
		}

		if (
			LoginService::mag('commissie:NovCie') &&
			$this->em->getFilters()->isEnabled('verbergNovieten')
		) {
			$this->em->getFilters()->disable('verbergNovieten');
		}
	}
}
