<?php

namespace CsrDelft\events;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use League\Bundle\OAuth2ServerBundle\Event\UserResolveEvent;

readonly final class UserResolveListener
{
	/**
	 * @param UserProviderInterface $userProvider
	 * @param UserPasswordHasherInterface $userPasswordHasher
	 */
	public function __construct(
		private UserProviderInterface $userProvider,
		private UserPasswordHasherInterface $userPasswordHasher
	) {
	}

	/**
	 * @param UserResolveEvent $event
	 */
	public function onUserResolve(UserResolveEvent $event): void
	{
		try {
			$user = $this->userProvider->loadUserByIdentifier($event->getUsername());
		} catch (UserNotFoundException) {
			return;
		}

		if (!$user instanceof UserInterface) {
			return;
		}

		if (!($user instanceof PasswordAuthenticatedUserInterface)) {
			return;
		}

		if (
			!$this->userPasswordHasher->isPasswordValid($user, $event->getPassword())
		) {
			return;
		}

		$event->setUser($user);
	}
}
