<?php

namespace CsrDelft\entity\fiscaat;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CiviPrijs
 *
 * Prijs van een @see CiviProduct van en tot zorgen ervoor dat altijd terug te vinden is wat de prijs van een product
 * was op een bepaald moment.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\fiscaat\CiviPrijsRepository::class
	)
]
#[ORM\Table('civi_prijs')]
#[
	ORM\UniqueConstraint(
		name: 'unique_van_product_id',
		columns: ['van', 'product_id']
	)
]
class CiviPrijs
{
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
	public $van;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime', nullable: true)]
	public $tot;
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	public $product_id;
	/**
	 * @var CiviProduct
	 */
	#[ORM\ManyToOne(targetEntity: \CiviProduct::class, inversedBy: 'prijzen')]
	public $product;
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	public $prijs;
}
