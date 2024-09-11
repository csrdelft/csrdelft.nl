<?php

namespace CsrDelft\common\Security;

use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

class PersistentTokenProvider implements TokenProviderInterface
{
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly RememberLoginRepository $rememberLoginRepository,
		private readonly ProfielRepository $profielRepository
	) {
	}

	public function loadTokenBySeries(string $series)
	{
		$token = $this->rememberLoginRepository->findOneBy(['series' => $series]);

		if (!$token) {
			throw new TokenNotFoundException();
		}

		return $token;
	}

	public function deleteTokenBySeries(string $series)
	{
		$token = $this->loadTokenBySeries($series);
		if ($token) {
			$this->entityManager->remove($token);
			$this->entityManager->flush();
		}
	}

	public function updateToken(
		string $series,
		string $tokenValue,
		\DateTime $lastUsed
	) {
		$token = $this->loadTokenBySeries($series);
		$token->token = $tokenValue;
		$token->last_used = $lastUsed;

		$this->entityManager->flush();
	}

	public function createNewToken(PersistentTokenInterface $token)
	{
		$persistentToken = new RememberLogin();
		$persistentToken->token = $token->getTokenValue();
		$persistentToken->series = $token->getSeries();
		$persistentToken->last_used = $token->getLastUsed();
		$persistentToken->remember_since = date_create_immutable();
		$persistentToken->uid = $token->getUsername();
		$persistentToken->profiel = $this->profielRepository->find(
			$token->getUsername()
		);

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$persistentToken->device_name = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$persistentToken->device_name = '';
		}

		if (isset($_SERVER['REMOTE_ADDR'])) {
			$persistentToken->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$persistentToken->ip = '';
		}

		$persistentToken->lock_ip = false;

		$this->entityManager->persist($persistentToken);
		$this->entityManager->flush();
	}
}
