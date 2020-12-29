<?php

namespace CsrDelft\entity\civimelder;

use CsrDelft\repository\civimelder\ReeksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReeksRepository::class)
 * @ORM\Table(name="civimelder_reeks")
 */
class Reeks extends ActiviteitEigenschappen {
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
	 * @ORM\Column(type="string", length=255)
	 */
	private $rechtenAanmaken;

	/**
	 * @ORM\OneToMany(targetEntity=Activiteit::class, mappedBy="reeks", orphanRemoval=true)
	 */
	private $activiteiten;

	public function __construct() {
		$this->activiteiten = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getNaam(): ?string {
		return $this->naam;
	}

	public function setNaam(string $naam): self {
		$this->naam = $naam;

		return $this;
	}

	public function getRechtenAanmaken(): ?string {
		return $this->rechtenAanmaken;
	}

	public function setRechtenAanmaken(string $rechtenAanmaken): self {
		$this->rechtenAanmaken = $rechtenAanmaken;

		return $this;
	}

	/**
	 * @return Collection|Activiteit[]
	 */
	public function getActiviteiten(): Collection {
		return $this->activiteiten;
	}

	public function addActiviteiten(Activiteit $activiteiten): self {
		if (!$this->activiteiten->contains($activiteiten)) {
			$this->activiteiten[] = $activiteiten;
			$activiteiten->setReeks($this);
		}

		return $this;
	}

	public function removeActiviteiten(Activiteit $activiteiten): self {
		if ($this->activiteiten->contains($activiteiten)) {
			$this->activiteiten->removeElement($activiteiten);
			// set the owning side to null (unless already changed)
			if ($activiteiten->getReeks() === $this) {
				$activiteiten->setReeks(null);
			}
		}

		return $this;
	}
}
