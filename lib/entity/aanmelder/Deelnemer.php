<?php

namespace CsrDelft\entity\aanmelder;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\aanmelder\DeelnemerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeelnemerRepository::class)]
#[ORM\Table(name: 'aanmelder_deelnemer')]
class Deelnemer
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $lid;

	#[ORM\Column(type: 'datetime')]
	private $aangemeld;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private $aanwezig = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getActiviteit(): AanmeldActiviteit
	{
		return $this->activiteit;
	}

	public function setActiviteit(?AanmeldActiviteit $activiteit): static
	{
		$this->activiteit = $activiteit;

		return $this;
	}

	public function getAantal(): int
	{
		return $this->aantal;
	}

	public function getLid(): Profiel
	{
		return $this->lid;
	}

	public function isAanwezig(): bool
	{
		return $this->aanwezig !== null;
	}
}
