<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
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
class CiviProduct implements DataTableEntry, DisplayEntity
{
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups({"datatable", "bar"})
	 */
	public $id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups({"datatable", "bar"})
	 */
	public $status;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 * @Serializer\Groups({"datatable", "bar"})
	 */
	public $beschrijving;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups({"datatable", "bar"})
	 */
	public $prioriteit;
	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups({"datatable", "bar"})
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

	/**
	 * @return string
	 * @Serializer\SerializedName("categorie")
	 * @Serializer\Groups("bar")
	 */
	public function getCategorieString()
	{
		return $this->categorie->getWeergave();
	}

	/**
	 * @return string
	 * @Serializer\Groups("bar")
	 */
	public function getCie()
	{
		return $this->categorie->cie;
	}

	public function __construct()
	{
		$this->prijzen = new ArrayCollection();
	}

	public function getUUID()
	{
		return $this->id . '@civiproduct.csrdelft.nl';
	}

	/**
	 * @return CiviPrijs
	 */
	public function getPrijs()
	{
		return $this->prijzen->last();
	}

	/**
	 * @return int
	 * @Serializer\Groups({"datatable", "bar"})
	 * @Serializer\SerializedName("prijs")
	 */
	public function getPrijsInt()
	{
		if ($prijs = $this->getPrijs()) {
			return $prijs->prijs;
		}

		return null;
	}

	/**
	 * Haalt de prijs van dit product op in een bepaald moment.
	 *
	 * @param \DateTimeInterface $moment
	 * @return false|mixed
	 */
	public function getPrijsOpMoment(\DateTimeInterface $moment)
	{
		$vanExpr = Criteria::expr()->lt('van', $moment);
		$totExpr = Criteria::expr()->orX(
			Criteria::expr()->gt('tot', $moment),
			Criteria::expr()->isNull('tot'),
		);
		$criteria = Criteria::create()
			->where(Criteria::expr()->andX($vanExpr, $totExpr));

		/** @var CiviPrijs $prijs */
		$prijs = $this->prijzen->matching($criteria)->first();

		return $prijs->prijs;
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("categorie")
	 */
	public function getDataTableCategorie()
	{
		return $this->categorie->getBeschrijving();
	}

	public function getBeschrijvingFormatted()
	{
		return sprintf("%s (€%.2f)", $this->beschrijving, $this->getPrijsInt() / 100);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->getBeschrijvingFormatted();
	}
}
