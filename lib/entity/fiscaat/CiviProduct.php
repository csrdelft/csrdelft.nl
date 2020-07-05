<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class CiviProduct
 *
 * Bevat een @see CiviPrijs
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\fiscaat\CiviProductRepository")
 */
class CiviProduct implements DataTableEntry, DisplayEntity {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("datatable")
	 */
	public $id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups("datatable")
	 */
	public $status;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 * @Serializer\Groups("datatable")
	 */
	public $beschrijving;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups("datatable")
	 */
	public $prioriteit;
	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups("datatable")
	 */
	public $beheer;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $categorie_id;
	/**
	 * @var CiviCategorie
	 * @ORM\ManyToOne(targetEntity="CiviCategorie")
	 */
	public $categorie;
	/**
	 * Tijdelijke placeholder
	 * @var integer
	 */
	public $tmpPrijs;
	/**
	 * @var CiviPrijs[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="CiviPrijs", mappedBy="product")
	 * @ORM\OrderBy({"van" = "ASC"})
	 */
	public $prijzen;

	public function __construct() {
		$this->prijzen = new ArrayCollection();
	}

	public function getUUID() {
		return $this->id . '@civiproduct.csrdelft.nl';
	}

	/**
	 * @return CiviPrijs
	 */
	public function getPrijs() {
		return $this->prijzen->last();
	}

	/**
	 * @return int
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("prijs")
	 */
	public function getPrijsInt() {
		if ($prijs = $this->getPrijs()) {
			return $prijs->prijs;
		}

		return null;
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("categorie")
	 */
	public function getDataTableCategorie() {
		return $this->categorie->getBeschrijving();
	}

	public function getBeschrijvingFormatted() {
		return sprintf("%s (€%.2f)", $this->beschrijving, $this->getPrijsInt() / 100);
	}

	public function getId() {
		return $this->id;
	}

	public function getWeergave(): string {
		return $this->getBeschrijvingFormatted();
	}
}
