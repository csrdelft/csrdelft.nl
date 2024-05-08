<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class CiviBestellingInhoud
 *
 * Onderdeel van een @see CiviBestelling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Entity(repositoryClass: CiviBestellingInhoudRepository::class)]
class CiviBestellingInhoud
{
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[Serializer\Groups('datatable')]
 public $bestelling_id;
	/**
  * @var CiviBestelling
  */
 #[ORM\ManyToOne(targetEntity: \CiviBestelling::class, inversedBy: 'inhoud')]
 public $bestelling;
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[Serializer\Groups(['datatable', 'bar'])]
 public $product_id;
	/**
  * @var CiviProduct
  */
 #[ORM\ManyToOne(targetEntity: \CiviProduct::class)]
 #[Serializer\Groups('bar')]
 public $product;
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[Serializer\Groups(['datatable', 'bar'])]
 public $aantal;

	public function setProduct(CiviProduct $product = null)
	{
		$this->product = $product;
		$this->product_id = $product->id ?? null;
	}

	public function setBestelling(CiviBestelling $bestelling = null)
	{
		$this->bestelling = $bestelling;
		$this->bestelling_id = $bestelling->id ?? null;
	}

	public function getBeschrijving(): string
	{
		return sprintf('%d %s', $this->aantal, $this->product->beschrijving);
	}

	/**
	 * @return int
	 */
	public function getPrijs()
	{
		return $this->product->getPrijsInt() * $this->aantal;
	}

	/**
  * @return string
  */
 #[Serializer\Groups('datatable')]
 public function getStukprijs(): string
	{
		return sprintf('€%.2f', $this->product->getPrijsInt() / 100);
	}

	/**
  * @return string
  */
 #[Serializer\Groups('datatable')]
 public function getTotaalprijs(): string
	{
		return sprintf('€%.2f', $this->getPrijs() / 100);
	}

	/**
  * @return string
  */
 #[Serializer\Groups('datatable')]
 #[Serializer\SerializedName('product')]
 public function getDataTableProduct()
	{
		return $this->product->beschrijving;
	}
}
