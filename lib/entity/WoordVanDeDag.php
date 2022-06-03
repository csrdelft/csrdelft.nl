<?php

namespace CsrDelft\entity;

use CsrDelft\Repository\WoordVanDeDagRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WoordVanDeDagRepository::class)
 */
class WoordVanDeDag
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $woord;


	public function getId(): ?int
	{
		return $this->id;
	}

	public function getWoord(): ?string
	{
		return $this->woord;
	}
}
