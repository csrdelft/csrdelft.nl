<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeclaratieCategorieRepository::class)
 */
class DeclaratieCategorie {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $naam;

	/**
	 * @ORM\ManyToOne(targetEntity=DeclaratieWachtrij::class, inversedBy="categorieen")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $wachtrij;

	/**
	 * @ORM\OneToMany(targetEntity=Declaratie::class, mappedBy="categorie")
	 */
	private $declaraties;

	public function __construct() {
		$this->declaraties = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getNaam(): string {
		return $this->naam;
	}

	public function setNaam(string $naam): self {
		$this->naam = $naam;

		return $this;
	}

	public function getWachtrij(): DeclaratieWachtrij {
		return $this->wachtrij;
	}

	public function setWachtrij(DeclaratieWachtrij $wachtrij): self {
		$this->wachtrij = $wachtrij;

		return $this;
	}

	/**
	 * @return Collection|Declaratie[]
	 */
	public function getDeclaraties(): Collection {
		return $this->declaraties;
	}

	public function addDeclaratie(Declaratie $declaratie): self {
		if (!$this->declaraties->contains($declaratie)) {
			$this->declaraties[] = $declaratie;
			$declaratie->setCategorie($this);
		}

		return $this;
	}

	public function removeDeclaratie(Declaratie $declaratie): self {
		if ($this->declaraties->contains($declaratie)) {
			$this->declaraties->removeElement($declaratie);
			// set the owning side to null (unless already changed)
			if ($declaratie->getCategorie() === $this) {
				$declaratie->setCategorie(null);
			}
		}

		return $this;
	}

	public function magBeoordelen(): bool {
		return $this->getWachtrij()->magBeoordelen();
	}
}
