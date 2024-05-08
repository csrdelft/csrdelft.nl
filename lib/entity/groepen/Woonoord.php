<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\enum\HuisStatus;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Woonoord.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\groepen\WoonoordenRepository::class)]
class Woonoord extends Groep implements HeeftSoort, HeeftMoment
{
	use GroepMoment;

	/**
  * Woonoord / Huis
  * @var HuisStatus
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'enumHuisStatus')]
 public $huisStatus;

	/**
  * Doet mee met Eetplan
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'boolean')]
 public $eetplan;

	public function getUrl(): string
	{
		return '/groepen/woonoorden/' . $this->id;
	}

	public function getSoort(): HuisStatus
	{
		return $this->huisStatus;
	}

	public function setSoort($soort): void
	{
		$this->huisStatus = $soort;
	}

	public function setSoortString($soort): void
	{
		$this->huisStatus = HuisStatus::from($soort);
	}
}
