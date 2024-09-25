<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieRegelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\ParameterBag;

#[ORM\Entity(repositoryClass: DeclaratieRegelRepository::class)]
class DeclaratieRegel
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\ManyToOne(targetEntity: DeclaratieBon::class, inversedBy: 'regels')]
	#[ORM\JoinColumn(nullable: false)]
	private $bon;

	#[ORM\Column(type: 'float', nullable: true)]
	private $bedrag;

	#[ORM\Column(type: 'boolean', nullable: true)]
	private $inclBtw;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $btw;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $omschrijving;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getBon(): ?DeclaratieBon
	{
		return $this->bon;
	}

	public function setBon(?DeclaratieBon $bon): self
	{
		$this->bon = $bon;

		return $this;
	}

	public function getBedrag(): ?float
	{
		return $this->bedrag;
	}

	public function setBedrag(?float $bedrag): self
	{
		$this->bedrag = $bedrag;

		return $this;
	}

	public function getInclBtw(): ?bool
	{
		return $this->inclBtw;
	}

	public function setInclBtw(?bool $inclBtw): self
	{
		$this->inclBtw = $inclBtw;

		return $this;
	}

	public function getBtw(): ?int
	{
		return $this->btw;
	}

	public function setBtw(?int $btw): self
	{
		$this->btw = $btw;

		return $this;
	}

	public function getOmschrijving(): ?string
	{
		return $this->omschrijving;
	}

	public function setOmschrijving(?string $omschrijving): self
	{
		$this->omschrijving = $omschrijving;

		return $this;
	}

	public function fromParameters(ParameterBag $regelData): self
	{
		$this->setOmschrijving(null);
		if ($regelData->get('omschrijving')) {
			$this->setOmschrijving($regelData->get('omschrijving'));
		}

		$this->setBedrag(null);
		if (is_numeric($regelData->get('bedrag'))) {
			$this->setBedrag(floatval($regelData->get('bedrag')));
		}

		$this->setInclBtw(null);
		$this->setBtw(null);
		switch ($regelData->get('btw')) {
			case 'incl. 9%':
				$this->setInclBtw(true);
				$this->setBtw(9);
				break;
			case 'incl. 21%':
				$this->setInclBtw(true);
				$this->setBtw(21);
				break;
			case 'excl. 9%':
				$this->setInclBtw(false);
				$this->setBtw(9);
				break;
			case 'excl. 21%':
				$this->setInclBtw(false);
				$this->setBtw(21);
				break;
			case 'geen: 0%':
				$this->setInclBtw(true);
				$this->setBtw(0);
				break;
		}

		return $this;
	}

	public function getBedragExcl(): float
	{
		if ($this->inclBtw === true) {
			return $this->bedrag / (1 + $this->btw / 100);
		} elseif ($this->inclBtw === false) {
			return $this->bedrag;
		} else {
			return 0;
		}
	}

	public function getBtwBedrag(): float
	{
		if ($this->btw) {
			return ($this->getBedragExcl() * $this->btw) / 100;
		} else {
			return 0;
		}
	}

	public function getBedragIncl(): float
	{
		if ($this->inclBtw === false) {
			return $this->bedrag * (1 + $this->btw / 100);
		} elseif ($this->inclBtw === true) {
			return $this->bedrag;
		} else {
			return 0;
		}
	}

	public function valideer($bonIndex, $regelIndex): array
	{
		$fouten = [];

		$toFill = [];
		if (empty($this->omschrijving)) {
			$toFill[] = 'de omschrijving';
		}
		if (empty($this->bedrag)) {
			$toFill[] = 'het bedrag';
		}
		if ($this->inclBtw === null) {
			$toFill[] = 'het btw-percentage';
		}

		if (!empty($toFill)) {
			$combined =
				implode(', ', array_slice($toFill, 0, count($toFill) - 1)) .
				(count($toFill) > 1 ? ' en ' : '') .
				$toFill[count($toFill) - 1];
			$fouten[] = "Bon $bonIndex, regel $regelIndex: vul $combined in";
		}

		return $fouten;
	}

	public function naarObject(): array
	{
		if ($this->btw === 0) {
			$btw = 'geen: 0%';
		} elseif ($this->inclBtw === true) {
			$btw = "incl. {$this->btw}%";
		} elseif ($this->inclBtw === false) {
			$btw = "excl. {$this->btw}%";
		} else {
			$btw = null;
		}

		return [
			'bedrag' => $this->bedrag,
			'btw' => $btw,
			'omschrijving' => $this->omschrijving,
		];
	}
}
