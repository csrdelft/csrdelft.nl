<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Kring.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\KringenRepository")
 */
class Kring extends Groep implements HeeftMoment
{
	use GroepMoment;

	/**
	 * Verticaleletter
	 * @var string
	 * @ORM\Column(type="string", length=1, options={"fixed"=true})
	 */
	#[Serializer\Groups(['datatable', 'log'])]
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	#[Serializer\Groups(['datatable', 'log'])]
	public $kringNummer;

	public function getUrl()
	{
		return '/groepen/kringen/' . $this->verticale . '.' . $this->kringNummer;
	}
}
