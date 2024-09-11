<?php

namespace CsrDelft\common\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class JwtTokenBadge implements BadgeInterface
{
	public function __construct(
		private readonly string $token,
		private readonly ?string $refreshToken
	) {
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
