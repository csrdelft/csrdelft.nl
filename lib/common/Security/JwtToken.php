<?php

namespace CsrDelft\common\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtToken extends AbstractToken
{
	public function __construct(
		UserInterface $user,
		private readonly string $token,
		/**
		 * @var string
		 */
		private readonly ?string $refreshToken,
		private readonly string $providerKey,
		array $roles = []
	) {
		parent::__construct($roles);

		$this->setUser($user);
		$this->setAuthenticated(true);
	}

	public function getCredentials()
	{
		return '';
	}

	/**
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * @return string
	 */
	public function getRefreshToken(): ?string
	{
		return $this->refreshToken;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __serialize(): array
	{
		return [
			$this->token,
			$this->refreshToken,
			$this->providerKey,
			parent::__serialize(),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function __unserialize(array $data): void
	{
		[
			$this->token,
			$this->refreshToken,
			$this->providerKey,
			$parentData,
		] = $data;
		$parentData = \is_array($parentData)
			? $parentData
			: unserialize($parentData);
		parent::__unserialize($parentData);
	}
}
