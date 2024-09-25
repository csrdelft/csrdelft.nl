<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Kring.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\groepen\KringenRepository::class
	)
]
class Kring extends Groep implements HeeftMoment
{
	use GroepMoment;

	/**
	 * Verticaleletter
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log'])]
	#[ORM\Column(type: 'string', length: 1, options: ['fixed' => true])]
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 */
	#[Serializer\Groups(['datatable', 'log'])]
	#[ORM\Column(type: 'integer')]
	public $kringNummer;

	public function getUrl()
	{
		return '/groepen/kringen/' . $this->verticale . '.' . $this->kringNummer;
	}
}
