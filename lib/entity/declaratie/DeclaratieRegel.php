<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieRegelRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeclaratieRegelRepository::class)
 */
class DeclaratieRegel {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=DeclaratieBon::class, inversedBy="regels")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $bon;

	/**
	 * @ORM\Column(type="float")
	 */
	private $bedrag;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $inclBtw;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $btw;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $omschrijving;

	public function getId(): ?int {
		return $this->id;
	}

	public function getBon(): ?DeclaratieBon {
		return $this->bon;
	}

	public function setBon(?DeclaratieBon $bon): self {
		$this->bon = $bon;

		return $this;
	}

	public function getBedrag(): float {
		return $this->bedrag;
	}

	public function setBedrag(float $bedrag): self {
		$this->bedrag = $bedrag;

		return $this;
	}

	public function getInclBtw(): bool {
		return $this->inclBtw;
	}

	public function setInclBtw(bool $inclBtw): self {
		$this->inclBtw = $inclBtw;

		return $this;
	}

	public function getBtw(): int {
		return $this->btw;
	}

	public function setBtw(int $btw): self {
		$this->btw = $btw;

		return $this;
	}

	public function getOmschrijving(): string {
		return $this->omschrijving;
	}

	public function setOmschrijving(string $omschrijving): self {
		$this->omschrijving = $omschrijving;

		return $this;
	}
}
