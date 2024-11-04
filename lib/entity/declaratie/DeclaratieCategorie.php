<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeclaratieCategorieRepository::class)]
class DeclaratieCategorie
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'string', length: 255)]
	private $naam;

	#[
		ORM\ManyToOne(
			targetEntity: DeclaratieWachtrij::class,
			inversedBy: 'categorieen'
		)
	]
	#[ORM\JoinColumn(nullable: false)]
	private $wachtrij;

	#[ORM\OneToMany(targetEntity: Declaratie::class, mappedBy: 'categorie')]
	private $declaraties;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getNaam(): string
	{
		return $this->naam;
	}

	public function getWachtrij(): DeclaratieWachtrij
	{
		return $this->wachtrij;
	}

	public function magBeoordelen(): bool
	{
		return $this->getWachtrij()->magBeoordelen();
	}
}
