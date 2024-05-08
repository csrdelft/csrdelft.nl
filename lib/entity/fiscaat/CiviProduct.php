<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\repository\fiscaat\CiviProductRepository;
use CiviCategorie;
use CiviPrijs;
use DateTimeInterface;
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
 */
#[ORM\Entity(repositoryClass: CiviProductRepository::class)]
class CiviProduct implements DataTableEntry, DisplayEntity
{
	/**
  * @var integer
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;
	/**
  * @var integer
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'integer')]
 public $status;
	/**
  * @var string
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'text')]
 public $beschrijving;
	/**
  * @var integer
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'integer')]
 public $prioriteit;
	/**
  * @var boolean
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'boolean')]
 public $beheer;
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 public $categorie_id;
	/**
  * @var CiviCategorie
  */
 #[ORM\ManyToOne(targetEntity: CiviCategorie::class)]
 public $categorie;
	/**
	 * Tijdelijke placeholder
	 * @var integer
	 */
	public $tmpPrijs;
	/**
  * @var CiviPrijs[]|ArrayCollection
  */
 #[ORM\OneToMany(targetEntity: CiviPrijs::class, mappedBy: 'product')]
 #[ORM\OrderBy(['van' => 'ASC'])]
 public $prijzen;

	/**
	 * @return string
	 * @Serializer\SerializedName("categorie")
	 * @Serializer\Groups("bar")
	 */
	public function getCategorieString(): string
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

	public function getUUID(): string
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
  * @param DateTimeInterface $moment
  * @return false|mixed
  */
 public function getPrijsOpMoment(DateTimeInterface $moment)
	{
		$vanExpr = Criteria::expr()->lt('van', $moment);
		$totExpr = Criteria::expr()->orX(
			Criteria::expr()->gt('tot', $moment),
			Criteria::expr()->isNull('tot')
		);
		$criteria = Criteria::create()->where(
			Criteria::expr()->andX($vanExpr, $totExpr)
		);

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

	public function getBeschrijvingFormatted(): string
	{
		return sprintf(
			'%s (€%.2f)',
			$this->beschrijving,
			$this->getPrijsInt() / 100
		);
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
