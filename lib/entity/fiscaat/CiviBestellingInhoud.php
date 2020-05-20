<?php

namespace CsrDelft\entity\fiscaat;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CiviBestellingInhoud
 *
 * Onderdeel van een @see CiviBestelling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository")
 * @ORM\Table("CiviBestellingInhoud")
 */
class CiviBestellingInhoud {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
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
	 */
	public $product_id;
	/**
	 * @var CiviProduct
	 * @ORM\ManyToOne(targetEntity="CiviProduct")
	 */
	public $product;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $aantal;

	public function setProduct(CiviProduct $product = null) {
		$this->product = $product;
		$this->product_id = $product->id ?? null;
	}

	public function setBestelling(CiviBestelling $bestelling = null) {
		$this->bestelling = $bestelling;
		$this->bestelling_id = $bestelling->id ?? null;
	}

	public function getBeschrijving() {
		return sprintf("%d %s", $this->aantal, $this->product->beschrijving);
	}

	/**
	 * @return int
	 */
	public function getPrijs() {
		return $this->product->tmpPrijs * $this->aantal;
	}

}
