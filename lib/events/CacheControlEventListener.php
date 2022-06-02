<?php


namespace CsrDelft\events;


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Security;

class CacheControlEventListener
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{
		$this->security = $security;
	}

	public function onKernelResponse(ResponseEvent $event)
	{
		// Voorkom caching van interne bestanden, zolang ze geen bestanden zijn.
		if ($this->security->getUser() && !($event->getResponse() instanceof BinaryFileResponse)) {
			$event->getResponse()->headers->set('Cache-Control', 'no-store');
		}
	}

}
