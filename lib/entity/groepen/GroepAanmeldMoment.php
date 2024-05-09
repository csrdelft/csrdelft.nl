<?php

namespace CsrDelft\entity\groepen;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepAanmeldMoment
{
	/**
  * Datum en tijd aanmeldperiode begin
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 #[Serializer\Groups('datatable')]
 public $aanmeldenVanaf;
	/**
  * Datum en tijd aanmeldperiode einde
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 #[Serializer\Groups(['datatable', 'vue'])]
 public $aanmeldenTot;
	/**
  * Datum en tijd aanmelding bewerken toegestaan
  * @var DateTimeImmutable|null
  */
 #[ORM\Column(type: 'datetime_immutable', nullable: true)]
 #[Serializer\Groups('datatable')]
 public $bewerkenTot;
	/**
  * Datum en tijd afmelden toegestaan
  * @var DateTimeImmutable|null
  */
 #[ORM\Column(type: 'datetime_immutable', nullable: true)]
 #[Serializer\Groups('datatable')]
 public $afmeldenTot;

	/**
	 * @return DateTimeImmutable
	 */
	public function getAanmeldenVanaf(): DateTimeImmutable
	{
		return $this->aanmeldenVanaf;
	}

	/**
	 * @param DateTimeImmutable $aanmeldenVanaf
	 */
	public function setAanmeldenVanaf(DateTimeImmutable $aanmeldenVanaf): void
	{
		$this->aanmeldenVanaf = $aanmeldenVanaf;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getAanmeldenTot(): ?DateTimeImmutable
	{
		return $this->aanmeldenTot;
	}

	/**
	 * @param DateTimeImmutable $aanmeldenTot
	 */
	public function setAanmeldenTot(DateTimeImmutable $aanmeldenTot): void
	{
		$this->aanmeldenTot = $aanmeldenTot;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getBewerkenTot(): ?DateTimeImmutable
	{
		return $this->bewerkenTot;
	}

	/**
	 * @param DateTimeImmutable|null $bewerkenTot
	 */
	public function setBewerkenTot(?DateTimeImmutable $bewerkenTot): void
	{
		$this->bewerkenTot = $bewerkenTot;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getAfmeldenTot(): ?DateTimeImmutable
	{
		return $this->afmeldenTot;
	}

	/**
	 * @param DateTimeImmutable|null $afmeldenTot
	 */
	public function setAfmeldenTot(?DateTimeImmutable $afmeldenTot): void
	{
		$this->afmeldenTot = $afmeldenTot;
	}
}
