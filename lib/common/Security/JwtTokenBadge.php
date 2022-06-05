<?php

namespace CsrDelft\common\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class JwtTokenBadge implements BadgeInterface
{
	/**
	 * @var string
	 */
	private $token;
	/**
	 * @var string|null
	 */
	private $refreshToken;

	public function __construct(string $token, ?string $refreshToken)
	{
		$this->token = $token;
		$this->refreshToken = $refreshToken;
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

	public function isResolved(): bool
	{
		return true;
	}
}
