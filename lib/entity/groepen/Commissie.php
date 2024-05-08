<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Commissie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een commissie is een groep waarvan de groepsleden een specifieke functie (kunnen) hebben.
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\groepen\CommissiesRepository::class)]
class Commissie extends Groep implements HeeftSoort, HeeftMoment
{
	use GroepMoment;
	/**
  * (Bestuurs-)Commissie / SjaarCie
  * @var CommissieSoort
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'enumCommissieSoort')]
 public $commissieSoort;

	public function getUrl(): string
	{
		return '/groepen/commissies/' . $this->id;
	}

	public function getSoort(): CommissieSoort
	{
		return $this->commissieSoort;
	}

	public function setSoort($soort): void
	{
		$this->commissieSoort = $soort;
	}

	public function setSoortString($soort): void
	{
		$this->commissieSoort = CommissieSoort::from($soort);
	}
}
