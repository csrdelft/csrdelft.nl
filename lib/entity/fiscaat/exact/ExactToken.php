<?php

namespace CsrDelft\entity\fiscaat\exact;

use CsrDelft\repository\fiscaat\exact\ExactTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExactTokenRepository::class)
 */
class ExactToken
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	private $accessToken;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	private $refreshToken;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	private $expires;


	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	/**
	 * @param string $accessToken
	 */
	public function setAccessToken(string $accessToken): void
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * @return string
	 */
	public function getRefreshToken(): string
	{
		return $this->refreshToken;
	}

	/**
	 * @param string $refreshToken
	 */
	public function setRefreshToken(string $refreshToken): void
	{
		$this->refreshToken = $refreshToken;
	}

	/**
	 * @return int
	 */
	public function getExpires(): int
	{
		return $this->expires;
	}

	/**
	 * @param int $expires
	 */
	public function setExpires(int $expires): void
	{
		$this->expires = $expires;
	}
}
