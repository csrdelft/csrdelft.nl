<?php


namespace CsrDelft\events;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class LogEventListener
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(LoggerInterface $logger, Security $security) {
		$this->logger = $logger;
		$this->security = $security;
	}

	/**
	 * Schrijft een log naar de 'access' logger
	 *
	 * @param RequestEvent $event
	 */
	public function onKernelRequest(RequestEvent $event) {
		$request = $event->getRequest();
		$user = $this->security->getUser();

		$this->logger->info($request->getRequestUri(), [
			'user' => $user ? $user->getUsername() : "x999",
			'ip' => $request->getClientIp(),
			'user-agent' => $request->headers->get('User-Agent'),
			'referer' => $request->headers->get('Referer'),
		]);
	}

}
