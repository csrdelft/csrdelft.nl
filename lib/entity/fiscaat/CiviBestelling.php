<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\common\Util\BedragUtil;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class CiviBestelling
 *
 * Heeft een of meer @see CiviBestellingInhoud
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\fiscaat\CiviBestellingRepository::class
	)
]
class CiviBestelling
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
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'uid')]
	public $uid;
	/**
	 * @var int
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'integer', options: ['default' => 0])]
	public $totaal = 0;
	/**
	 * @var boolean
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'boolean', options: ['default' => false])]
	public $deleted;
	/**
	 * @var \DateTimeImmutable
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
	public $moment;
	/**
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'string', nullable: true)]
	public $comment; // TODO dit is een CiviSaldoCommissieEnum
	/**
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'bar'])]
	#[ORM\Column(type: 'string')]
	public $cie;
	/**
	 * @var CiviBestellingInhoud[]|ArrayCollection
	 */
	#[Serializer\Groups('bar')]
	#[
		ORM\OneToMany(
			targetEntity: \CiviBestellingInhoud::class,
			mappedBy: 'bestelling'
		)
	]
	public $inhoud;

	/**
	 * @var CiviSaldo
	 */
	#[ORM\ManyToOne(targetEntity: \CiviSaldo::class, inversedBy: 'bestellingen')]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $civiSaldo;

	public function __construct()
	{
		$this->inhoud = new ArrayCollection();
	}

	/**
	 * @param $product_id
	 *
	 * @return CiviBestellingInhoud|null
	 *
	 * @psalm-param 24|151 $product_id
	 */
	public function getProduct(int $product_id)
	{
		$product = $this->inhoud->matching(
			Criteria::create()
				->where(Criteria::expr()->eq('product_id', $product_id))
				->setMaxResults(1)
		);

		if (count($product) !== 1) {
			return null;
		}

		return $product->first();
	}
}
