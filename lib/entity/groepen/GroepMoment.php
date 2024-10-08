<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @see HeeftMoment
 */
trait GroepMoment
{
	/**
	 * Datum en tijd begin
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'datetime')]
	public $beginMoment;
	/**
	 * Datum en tijd einde
	 * @var DateTimeImmutable|null
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'datetime', nullable: true)]
	public $eindMoment;

	/**
	 * @return DateTimeImmutable
	 */
	public function getBeginMoment(): DateTimeImmutable
	{
		return $this->beginMoment;
	}

	/**
	 * @param DateTimeImmutable $beginMoment
	 */
	public function setBeginMoment(DateTimeImmutable $beginMoment): void
	{
		$this->beginMoment = $beginMoment;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getEindMoment(): DateTimeImmutable
	{
		if ($this->eindMoment && $this->eindMoment !== $this->beginMoment) {
			return $this->eindMoment;
		}
		return $this->getBeginMoment()->add(new \DateInterval('PT30M'));
	}

	/**
	 * @param DateTimeImmutable $eindMoment
	 */
	public function setEindMoment(DateTimeImmutable $eindMoment): void
	{
		$this->eindMoment = $eindMoment;
	}
}
