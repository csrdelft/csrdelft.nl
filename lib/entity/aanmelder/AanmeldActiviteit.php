<?php

namespace CsrDelft\entity\aanmelder;

use CsrDelft\common\Eisen;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\repository\aanmelder\AanmeldActiviteitRepository;
use CsrDelft\service\security\LoginService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: AanmeldActiviteitRepository::class)]
#[ORM\Table(name: 'aanmelder_activiteit')]
class AanmeldActiviteit extends ActiviteitEigenschappen implements
	DataTableEntry
{
	#[Serializer\Groups(['datatable'])]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	public $id;

	#[ORM\ManyToOne(targetEntity: Reeks::class, inversedBy: 'activiteiten')]
	#[ORM\JoinColumn(nullable: false)]
	private $reeks;

	#[ORM\Column(type: 'datetime')]
	private $start;

	#[ORM\Column(type: 'datetime')]
	private $einde;

	#[ORM\Column(type: 'boolean')]
	private $gesloten;

	/**
	 * @var ArrayCollection|Deelnemer[]
	 */
	#[
		ORM\OneToMany(
			targetEntity: Deelnemer::class,
			mappedBy: 'activiteit',
			orphanRemoval: true
		)
	]
	private $deelnemers;

	public function __construct()
	{
		$this->deelnemers = new ArrayCollection();
	}

	// Getters & setters
	public function getId(): ?int
	{
		return $this->id;
	}

	public function getReeks(): ?Reeks
	{
		return $this->reeks;
	}

	public function setReeks(?Reeks $reeks): static
	{
		$this->reeks = $reeks;

		return $this;
	}

	public function getStart(): ?DateTimeImmutable
	{
		return $this->start;
	}

	public function getEinde(): ?DateTimeImmutable
	{
		return $this->einde;
	}

	public function isGesloten(): ?bool
	{
		return $this->gesloten;
	}

	public function setGesloten(bool $gesloten): static
	{
		$this->gesloten = $gesloten;

		return $this;
	}

	/**
	 * @return ArrayCollection|Deelnemer[]
	 *
	 * @psalm-return ArrayCollection|array<Deelnemer>
	 */
	public function getDeelnemers(): array|ArrayCollection
	{
		return $this->deelnemers;
	}

	// Eigenschappen


	public function getBeschrijving(): string|null
	{
		return $this->getRawBeschrijving() ?:
			$this->getReeks()->getRawBeschrijving();
	}

	public function getCapaciteit(): int|null
	{
		return $this->getRawCapaciteit() ?: $this->getReeks()->getRawCapaciteit();
	}

	public function getRechtenAanmelden(): string|null
	{
		return $this->getRawRechtenAanmelden() ?:
			$this->getReeks()->getRawRechtenAanmelden();
	}

	public function getRechtenLijstBekijken(): string|null
	{
		return $this->getRawRechtenLijstBekijken() ?:
			$this->getReeks()->getRawRechtenLijstBekijken();
	}

	public function getRechtenLijstBeheren(): string|null
	{
		return $this->getRawRechtenLijstBeheren() ?:
			$this->getReeks()->getRawRechtenLijstBeheren();
	}

	public function getMaxGasten(): int|null
	{
		return $this->getRawMaxGasten() ?: $this->getReeks()->getRawMaxGasten();
	}

	public function isAanmeldenMogelijk(): bool|null
	{
		return $this->isRawAanmeldenMogelijk() ?:
			$this->getReeks()->isRawAanmeldenMogelijk();
	}

	public function getAanmeldenVanaf(): ?int
	{
		return $this->getRawAanmeldenVanaf() ?:
			$this->getReeks()->getRawAanmeldenVanaf();
	}

	public function getAanmeldenTot(): ?int
	{
		return $this->getRawAanmeldenTot() ?:
			$this->getReeks()->getRawAanmeldenTot();
	}

	public function isAfmeldenMogelijk(): bool|null
	{
		return $this->isRawAfmeldenMogelijk() ?:
			$this->getReeks()->isRawAfmeldenMogelijk();
	}

	public function getAfmeldenTot(): ?int
	{
		return $this->getRawAfmeldenTot() ?: $this->getReeks()->getRawAfmeldenTot();
	}

	// Tijden afmelden
	private function getTijdVoor(int $minutes): DateTimeImmutable
	{
		/** @noinspection PhpUnhandledExceptionInspection Minuten is altijd aantal minuten als integer */
		$tijd = new DateInterval('PT' . $minutes . 'M');
		return $this->getEinde()->sub($tijd);
	}

	public function getStartAanmelden(): DateTimeImmutable
	{
		return $this->getTijdVoor($this->getAanmeldenVanaf());
	}

	public function getEindAanmelden(): DateTimeImmutable
	{
		return $this->getTijdVoor($this->getAanmeldenTot());
	}

	public function getEindAfmelden(): DateTimeImmutable
	{
		return $this->getTijdVoor($this->getAfmeldenTot());
	}

	// Aanmeldingen
	public function getAantalAanmeldingen(): int
	{
		$aantal = 0;
		foreach ($this->deelnemers as $deelnemer) {
			$aantal += $deelnemer->getAantal();
		}

		return $aantal;
	}

	/**
	 * @psalm-return int<0, max>
	 */
	public function getResterendeCapaciteit(): int
	{
		return max($this->getCapaciteit() - $this->getAantalAanmeldingen(), 0);
	}

	// Rechten


	public function magLijstBekijken(): bool
	{
		return $this->magLijstBeheren() ||
			LoginService::mag($this->getRechtenLijstBekijken());
	}

	public function magLijstBeheren(): bool
	{
		return $this->getReeks()->magActiviteitenBeheren() ||
			LoginService::mag($this->getRechtenLijstBeheren());
	}

	public function magAanmelden(int $aantal, string &$reden = null): bool
	{
		$nu = date_create_immutable();
		if (
			$this->isGesloten() ||
			$nu < $this->getStartAanmelden() ||
			$nu >= $this->getEindAanmelden()
		) {
			$reden = 'activiteit is gesloten';
		} elseif (!$this->isAanmeldenMogelijk()) {
			$reden = 'aanmelden niet toegestaan voor deze activiteit';
		} elseif (!LoginService::mag($this->getRechtenAanmelden())) {
			$reden = 'geen rechten om aan te melden';
		} elseif ($this->getResterendeCapaciteit() < $aantal) {
			$reden = 'activiteit is vol';
		} else {
			return true;
		}

		return false;
	}

	public function magAfmelden(string &$reden = null): bool
	{
		$nu = date_create_immutable();
		if (
			$this->isGesloten() ||
			$nu < $this->getStartAanmelden() ||
			$nu >= $this->getEindAfmelden()
		) {
			$reden = 'activiteit is gesloten';
		} elseif (!$this->isAfmeldenMogelijk()) {
			$reden = 'afmelden niet toegestaan voor deze activiteit';
		} else {
			return true;
		}

		return false;
	}

	public function isInToekomst(): bool
	{
		$nu = date_create_immutable();
		return $nu < $this->getEinde();
	}

	public function isAangemeld(): bool
	{
		return $this->deelnemers->matching(Eisen::voorIngelogdLid())->count() == 1;
	}

	public function aantalGasten(): int
	{
		/** @var Deelnemer $deelnemer */
		$deelnemer = $this->deelnemers->matching(Eisen::voorIngelogdLid())->first();
		if ($deelnemer) {
			return $deelnemer->getAantal();
		}
		return 0;
	}
}
