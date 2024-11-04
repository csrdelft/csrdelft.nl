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

	// Tijden afmelden


	// Aanmeldingen
	public function getAantalAanmeldingen(): int
	{
		$aantal = 0;
		foreach ($this->deelnemers as $deelnemer) {
			$aantal += $deelnemer->getAantal();
		}

		return $aantal;
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

	public function isInToekomst(): bool
	{
		$nu = date_create_immutable();
		return $nu < $this->getEinde();
	}
}
