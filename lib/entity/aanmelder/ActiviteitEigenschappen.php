<?php

namespace CsrDelft\entity\aanmelder;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class ActiviteitEigenschappen
{
	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $titel;

	#[ORM\Column(type: 'text', nullable: true)]
	private $beschrijving;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $capaciteit;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $rechtenAanmelden;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $rechtenLijstBekijken;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $rechtenLijstBeheren;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $maxGasten;

	#[ORM\Column(type: 'boolean', nullable: true)]
	private $aanmeldenMogelijk;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $aanmeldenVanaf;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $aanmeldenTot;

	#[ORM\Column(type: 'boolean', nullable: true)]
	private $afmeldenMogelijk;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $afmeldenTot;

	public function getRawTitel(): ?string
	{
		return $this->titel;
	}

	public function getRawBeschrijving(): ?string
	{
		return $this->beschrijving;
	}

	public function getRawCapaciteit(): ?int
	{
		return $this->capaciteit;
	}

	public function getRawRechtenAanmelden(): ?string
	{
		return $this->rechtenAanmelden;
	}

	public function getRawRechtenLijstBekijken(): ?string
	{
		return $this->rechtenLijstBekijken;
	}

	public function getRawRechtenLijstBeheren(): ?string
	{
		return $this->rechtenLijstBeheren;
	}

	public function getRawMaxGasten(): ?int
	{
		return $this->maxGasten;
	}

	public function isRawAanmeldenMogelijk(): ?bool
	{
		return $this->aanmeldenMogelijk;
	}

	public function getRawAanmeldenVanaf(): ?int
	{
		return $this->aanmeldenVanaf;
	}

	public function getRawAanmeldenTot(): ?int
	{
		return $this->aanmeldenTot;
	}

	public function isRawAfmeldenMogelijk(): ?bool
	{
		return $this->afmeldenMogelijk;
	}

	public function getRawAfmeldenTot(): ?int
	{
		return $this->afmeldenTot;
	}
}
