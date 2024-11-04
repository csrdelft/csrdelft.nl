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
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\fiscaat\CiviProductRepository::class
	)
]
class CiviProduct implements DataTableEntry, DisplayEntity
{
	/**
	 * @var integer
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var integer
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'integer')]
	public $status;
	/**
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'text')]
	public $beschrijving;
	/**
	 * @var integer
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'integer')]
	public $prioriteit;
	/**
	 * @var boolean
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
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
	#[ORM\ManyToOne(targetEntity: \CiviCategorie::class)]
	public $categorie;
	/**
	 * Tijdelijke placeholder
	 * @var integer
	 */
	public $tmpPrijs;
	/**
	 * @var CiviPrijs[]|ArrayCollection
	 */
	#[ORM\OneToMany(targetEntity: \CiviPrijs::class, mappedBy: 'product')]
	#[ORM\OrderBy(['van' => 'ASC'])]
	public $prijzen;

	public function __construct()
	{
		$this->prijzen = new ArrayCollection();
	}

	/**
	 * @return false|mixed
	 *
	 * @psalm-return T|false
	 */
	public function getPrijs()
	{
		return $this->prijzen->last();
	}

	#[Serializer\Groups(['datatable', 'bar'])]
	#[Serializer\SerializedName('prijs')]
	public function getPrijsInt(): int|null
	{
		if ($prijs = $this->getPrijs()) {
			return $prijs->prijs;
		}

		return null;
	}

	public function getBeschrijvingFormatted(): string
	{
		return sprintf(
			'%s (€%.2f)',
			$this->beschrijving,
			$this->getPrijsInt() / 100
		);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->getBeschrijvingFormatted();
	}
}
