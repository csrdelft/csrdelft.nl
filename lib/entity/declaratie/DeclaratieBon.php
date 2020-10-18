<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieBonRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeclaratieBonRepository::class)
 */
class DeclaratieBon {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $bestand;

	/**
	 * @ORM\ManyToOne(targetEntity=Declaratie::class, inversedBy="bonnen")
	 */
	private $declaratie;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $datum;

	/**
	 * @ORM\OneToMany(targetEntity=DeclaratieRegel::class, mappedBy="bon")
	 */
	private $regels;

	public function __construct() {
		$this->regels = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getBestand(): string {
		return $this->bestand;
	}

	public function setBestand(string $bestand): self {
		$this->bestand = $bestand;

		return $this;
	}

	public function getDeclaratie(): ?Declaratie {
		return $this->declaratie;
	}

	public function setDeclaratie(?Declaratie $declaratie): self {
		$this->declaratie = $declaratie;

		return $this;
	}

	public function getDatum(): ?DateTimeInterface {
		return $this->datum;
	}

	public function setDatum(?DateTimeInterface $datum): self {
		$this->datum = $datum;

		return $this;
	}

	/**
	 * @return Collection|DeclaratieRegel[]
	 */
	public function getRegels(): Collection {
		return $this->regels;
	}

	public function addRegel(DeclaratieRegel $regel): self {
		if (!$this->regels->contains($regel)) {
			$this->regels[] = $regel;
			$regel->setBon($this);
		}

		return $this;
	}

	public function removeRegel(DeclaratieRegel $regel): self {
		if ($this->regels->contains($regel)) {
			$this->regels->removeElement($regel);
			// set the owning side to null (unless already changed)
			if ($regel->getBon() === $this) {
				$regel->setBon(null);
			}
		}

		return $this;
	}
}
