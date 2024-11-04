<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieWachtrijRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeclaratieWachtrijRepository::class)]
class DeclaratieWachtrij
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'string', length: 255)]
	private $naam;

	#[ORM\Column(type: 'string', length: 255)]
	private $rechten;

	#[ORM\Column(type: 'integer')]
	private $positie;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $email;

	#[ORM\Column(type: 'string', length: 2, nullable: true)]
	private $prefix;

	#[
		ORM\OneToMany(
			targetEntity: DeclaratieCategorie::class,
			mappedBy: 'wachtrij'
		)
	]
	private $categorieen;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getNaam(): string
	{
		return $this->naam;
	}

	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	public function magBeoordelen(): bool
	{
		return LoginService::mag($this->rechten) || Declaratie::isFiscus();
	}
}
