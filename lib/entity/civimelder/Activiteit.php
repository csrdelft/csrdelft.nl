<?php

namespace CsrDelft\entity\civimelder;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;
use CsrDelft\service\security\LoginService;
use DateInterval;
use DateTimeImmutable;
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

	// Getters & setters
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

	public function getStart(): ?DateTimeImmutable {
		return $this->start;
	}

	public function setStart(DateTimeImmutable $start): self {
		$this->start = $start;

		return $this;
	}

	public function getEinde(): ?DateTimeImmutable {
		return $this->einde;
	}

	public function setEinde(DateTimeImmutable $einde): self {
		$this->einde = $einde;

		return $this;
	}

	public function isGesloten(): ?bool {
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

	// Eigenschappen
	public function getTitel(): string {
		return $this->getRawTitel() ?: $this->getReeks()->getRawTitel();
	}

	public function getBeschrijving(): string {
		return $this->getRawBeschrijving() ?: $this->getReeks()->getRawBeschrijving();
	}

	public function getCapaciteit(): int {
		return $this->getRawCapaciteit() ?: $this->getReeks()->getRawCapaciteit();
	}

	public function getRechtenAanmelden(): string {
		return $this->getRawRechtenAanmelden() ?: $this->getReeks()->getRawRechtenAanmelden();
	}

	public function getRechtenLijstBekijken(): string {
		return $this->getRawRechtenLijstBekijken() ?: $this->getReeks()->getRawRechtenLijstBekijken();
	}

	public function getRechtenLijstBeheren(): string {
		return $this->getRawRechtenLijstBeheren() ?: $this->getReeks()->getRawRechtenLijstBeheren();
	}

	public function getMaxGasten(): int {
		return $this->getRawMaxGasten() ?: $this->getReeks()->getRawMaxGasten();
	}

	public function isAanmeldenMogelijk(): bool {
		return $this->isRawAanmeldenMogelijk() ?: $this->getReeks()->isRawAanmeldenMogelijk();
	}

	public function getAanmeldenVanaf(): ?int {
		return $this->getRawAanmeldenVanaf() ?: $this->getReeks()->getRawAanmeldenVanaf();
	}

	public function getAanmeldenTot(): ?int {
		return $this->getRawAanmeldenTot() ?: $this->getReeks()->getRawAanmeldenTot();
	}

	public function isAfmeldenMogelijk(): bool {
		return $this->isRawAfmeldenMogelijk() ?: $this->getReeks()->isRawAfmeldenMogelijk();
	}

	public function getAfmeldenTot(): ?int {
		return $this->getRawAfmeldenTot() ?: $this->getReeks()->getRawAfmeldenTot();
	}

	// Tijden afmelden
	private function getTijdVoor(int $minutes): DateTimeImmutable {
		/** @noinspection PhpUnhandledExceptionInspection Minuten is altijd aantal minuten als integer */
		$tijd = new DateInterval('PT' . $minutes . 'M');
		return $this->getStart()->sub($tijd);
	}

	public function getStartAanmelden(): DateTimeImmutable {
		return $this->getTijdVoor($this->getAanmeldenVanaf());
	}

	public function getEindAanmelden(): DateTimeImmutable {
		return $this->getTijdVoor($this->getAanmeldenTot());
	}

	public function getEindAfmelden(): DateTimeImmutable {
		return $this->getTijdVoor($this->getAfmeldenTot());
	}

	// Aanmeldingen
	public function getAantalAanmeldingen(): int {
		$deelnemerRepository = ContainerFacade::getContainer()->get(DeelnemerRepository::class);
		return $deelnemerRepository->getAantalAanmeldingen($this);
	}

	public function getResterendeCapaciteit(): int {
		return max($this->getCapaciteit() - $this->getAantalAanmeldingen(), 0);
	}

	// Rechten
	public function magBekijken(): bool {
		return $this->magLijstBekijken()
			|| LoginService::mag($this->getRechtenAanmelden())
			|| ContainerFacade::getContainer()->get(DeelnemerRepository::class)
						->isAangemeld($this, LoginService::getProfiel());
	}

	public function magAanpassen(): bool {
		return $this->getReeks()->magActiviteitenBeheren();
	}

	public function magLijstBekijken(): bool {
		return $this->magLijstBeheren() || LoginService::mag($this->getRechtenLijstBekijken());
	}

	public function magLijstBeheren(): bool {
		return $this->getReeks()->magActiviteitenBeheren() || LoginService::mag($this->getRechtenLijstBeheren());
	}

	public function magAanmelden($aantal): bool {
		$nu = date_create_immutable();
		return !$this->isGesloten()
			&& $this->isAanmeldenMogelijk()
			&& LoginService::mag($this->getRechtenAanmelden())
			&& $nu >= $this->getStartAanmelden()
			&& $nu < $this->getEindAanmelden()
			&& $this->getResterendeCapaciteit() >= $aantal;
	}

	public function magAfmelden(): bool {
		$nu = date_create_immutable();
		return !$this->isGesloten()
			&& $this->isAfmeldenMogelijk()
			&& $nu >= $this->getStartAanmelden()
			&& $nu < $this->getEindAanmelden();
	}
}
