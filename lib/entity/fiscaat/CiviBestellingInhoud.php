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
 * @ORM\Entity(repositoryClass="CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository")
 */
class CiviBestellingInhoud
{
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @Serializer\Groups("datatable")
	 */
	public $bestelling_id;
	/**
	 * @var CiviBestelling
	 * @ORM\ManyToOne(targetEntity="CiviBestelling", inversedBy="inhoud")
	 */
	public $bestelling;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @Serializer\Groups({"datatable", "bar"})
	 */
	public $product_id;
	/**
	 * @var CiviProduct
	 * @ORM\ManyToOne(targetEntity="CiviProduct")
	 * @Serializer\Groups("bar")
	 */
	public $product;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups({"datatable", "bar"})
	 */
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

	public function getBeschrijving()
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
	 * @Serializer\Groups("datatable")
	 */
	public function getStukprijs()
	{
		return sprintf('€%.2f', $this->product->getPrijsInt() / 100);
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 */
	public function getTotaalprijs()
	{
		return sprintf('€%.2f', $this->getPrijs() / 100);
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("product")
	 */
	public function getDataTableProduct()
	{
		return $this->product->beschrijving;
	}
}
