<?php

namespace CsrDelft\entity\fiscaat;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class CiviBestellingInhoud
 *
 * Onderdeel van een @see CiviBestelling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository::class
	)
]
class CiviBestellingInhoud
{
	/**
	 * @var integer
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $bestelling_id;
	/**
	 * @var CiviBestelling
	 */
	#[ORM\ManyToOne(targetEntity: \CiviBestelling::class, inversedBy: 'inhoud')]
	public $bestelling;
	/**
	 * @var integer
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $product_id;
	/**
	 * @var CiviProduct
	 */
	#[Serializer\Groups('bar')]
	#[ORM\ManyToOne(targetEntity: \CiviProduct::class)]
	public $product;
	/**
	 * @var integer
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'integer')]
	public $aantal;

	/**
	 * @return int
	 */
	public function getPrijs()
	{
		return $this->product->getPrijsInt() * $this->aantal;
	}
}
