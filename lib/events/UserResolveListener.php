<?php

namespace CsrDelft\events;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\UserResolveEvent;

final class UserResolveListener
{
	/**
	 * @var UserProviderInterface
	 */
	private $userProvider;

	/**
	 * @var UserPasswordHasherInterface
	 */
	private $userPasswordHasher;

	/**
	 * @param UserProviderInterface $userProvider
	 * @param UserPasswordHasherInterface $userPasswordHasher
	 */
	public function __construct(
		UserProviderInterface $userProvider,
		UserPasswordHasherInterface $userPasswordHasher
	) {
		$this->userProvider = $userProvider;
		$this->userPasswordHasher = $userPasswordHasher;
	}

	/**
	 * @param UserResolveEvent $event
	 */
	public function onUserResolve(UserResolveEvent $event): void
	{
		try {
			$user = $this->userProvider->loadUserByIdentifier($event->getUsername());
		} catch (UserNotFoundException $ex) {
			return;
		}

		if (null === $user) {
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
