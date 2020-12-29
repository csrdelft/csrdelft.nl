<?php

namespace CsrDelft\entity\civimelder;

use CsrDelft\repository\civimelder\ActiviteitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActiviteitRepository::class)
 * @ORM\Table(name="civimelder_activiteit")
 */
class Activiteit extends ActiviteitEigenschappen {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Reeks::class, inversedBy="activiteiten")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $reeks;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $start;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $einde;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $gesloten;

	/**
	 * @ORM\OneToMany(targetEntity=Deelnemer::class, mappedBy="activiteit", orphanRemoval=true)
	 */
	private $deelnemers;

	public function __construct() {
		$this->deelnemers = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getReeks(): ?Reeks {
		return $this->reeks;
	}

	public function setReeks(?Reeks $reeks): self {
		$this->reeks = $reeks;

		return $this;
	}

	public function getStart(): ?\DateTimeInterface {
		return $this->start;
	}

	public function setStart(\DateTimeInterface $start): self {
		$this->start = $start;

		return $this;
	}

	public function getEinde(): ?\DateTimeInterface {
		return $this->einde;
	}

	public function setEinde(\DateTimeInterface $einde): self {
		$this->einde = $einde;

		return $this;
	}

	public function getGesloten(): ?bool {
		return $this->gesloten;
	}

	public function setGesloten(bool $gesloten): self {
		$this->gesloten = $gesloten;

		return $this;
	}

	/**
	 * @return Collection|Deelnemer[]
	 */
	public function getDeelnemers(): Collection {
		return $this->deelnemers;
	}

	public function addDeelnemer(Deelnemer $deelnemer): self {
		if (!$this->deelnemers->contains($deelnemer)) {
			$this->deelnemers[] = $deelnemer;
			$deelnemer->setActiviteit($this);
		}

		return $this;
	}

	public function removeDeelnemer(Deelnemer $deelnemer): self {
		if ($this->deelnemers->contains($deelnemer)) {
			$this->deelnemers->removeElement($deelnemer);
			// set the owning side to null (unless already changed)
			if ($deelnemer->getActiviteit() === $this) {
				$deelnemer->setActiviteit(null);
			}
		}

		return $this;
	}

	public function getInheritedTitel(): string {
		return $this->getTitel() ?: $this->getReeks()->getTitel();
	}

	public function getInheritedBeschrijving(): string {
		return $this->getBeschrijving() ?: $this->getReeks()->getBeschrijving();
	}

	public function getInheritedCapaciteit(): int {
		return $this->getCapaciteit() ?: $this->getReeks()->getCapaciteit();
	}

	public function getInheritedRechtenAanmelden(): string {
		return $this->getRechtenAanmelden() ?: $this->getReeks()->getRechtenAanmelden();
	}

	public function getInheritedRechtenLijstBekijken(): string {
		return $this->getRechtenLijstBekijken() ?: $this->getReeks()->getRechtenLijstBekijken();
	}

	public function getInheritedRechtenLijstBeheren(): string {
		return $this->getRechtenLijstBeheren() ?: $this->getReeks()->getRechtenLijstBeheren();
	}

	public function getInheritedMaxGasten(): int {
		return $this->getMaxGasten() ?: $this->getReeks()->getMaxGasten();
	}

	public function getInheritedAanmeldenMogelijk(): bool {
		return $this->getAanmeldenMogelijk() ?: $this->getReeks()->getAanmeldenMogelijk();
	}

	public function getInheritedAanmeldenVanaf(): ?int {
		return $this->getAanmeldenVanaf() ?: $this->getReeks()->getAanmeldenVanaf();;
	}

	public function getInheritedAanmeldenTot(): ?int {
		return $this->getAanmeldenTot() ?: $this->getReeks()->getAanmeldenTot();
	}

	public function getInheritedAfmeldenMogelijk(): bool {
		return $this->isAfmeldenMogelijk() ?: $this->getReeks()->isAfmeldenMogelijk();
	}

	public function getInheritedAfmeldenTot(): ?int {
		return $this->getAfmeldenTot() ?: $this->getReeks()->getAfmeldenTot();
	}

	public function getInheritedVoorwaarden(): array {
		return $this->getVoorwaarden() ?: $this->getReeks()->getVoorwaarden();
	}
}
