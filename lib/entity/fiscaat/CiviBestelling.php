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
#[ORM\Entity(repositoryClass: \CsrDelft\repository\fiscaat\CiviBestellingRepository::class)]
class CiviBestelling
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
  * @var string
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'uid')]
 public $uid;
	/**
  * @var int
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'integer', options: ['default' => 0])]
 public $totaal = 0;
	/**
  * @var boolean
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'boolean', options: ['default' => false])]
 public $deleted;
	/**
  * @var \DateTimeImmutable
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
 public $moment;
	/**
  * @var string
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $comment;
	/**
  * @var string
  * @Serializer\Groups({"datatable", "bar"})
  */
 #[ORM\Column(type: 'string')] // TODO dit is een CiviSaldoCommissieEnum
 public $cie;
	/**
  * @var CiviBestellingInhoud[]|ArrayCollection
  * @Serializer\Groups("bar")
  */
 #[ORM\OneToMany(targetEntity: \CiviBestellingInhoud::class, mappedBy: 'bestelling')]
 public $inhoud;

	/**
  * @var CiviSaldo
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: \CiviSaldo::class, inversedBy: 'bestellingen')]
 public $civiSaldo;

	public function __construct()
	{
		$this->inhoud = new ArrayCollection();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("inhoud")
	 */
	public function getInhoudTekst()
	{
		$bestellingenInhoud = [];
		foreach ($this->inhoud as $item) {
			$bestellingenInhoud[] = $item->getBeschrijving();
		}
		return implode(', ', $bestellingenInhoud);
	}

	/**
	 * @return string
	 */
	public function getPinBeschrijving()
	{
		$pinProduct = $this->getProduct(CiviProductTypeEnum::PINTRANSACTIE);

		if ($pinProduct === null) {
			$pinCorrectieProduct = $this->getProduct(
				CiviProductTypeEnum::PINCORRECTIE
			);
			if ($pinCorrectieProduct) {
				return BedragUtil::format_bedrag($pinCorrectieProduct->aantal) .
					' pincorrectie';
			} else {
				return '';
			}
		}

		$beschrijving = BedragUtil::format_bedrag($pinProduct->aantal) . ' PIN';

		$aantalInhoud = count($this->inhoud);

		if ($aantalInhoud == 2) {
			$beschrijving .= ' en 1 ander product';
		} elseif ($aantalInhoud > 2) {
			$beschrijving .= sprintf(' en %d andere producten', $aantalInhoud - 1);
		}

		return $beschrijving;
	}

	/**
	 * @param $product_id
	 * @return CiviBestellingInhoud|null
	 */
	public function getProduct($product_id)
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

	/**
	 * Bereken de prijs van deze bestelling opnieuw.
	 *
	 * @return int
	 */
	public function berekenTotaal()
	{
		$totaal = 0;

		foreach ($this->inhoud as $item) {
			$totaal +=
				$item->aantal * $item->product->getPrijsOpMoment($this->moment);
		}

		return $totaal;
	}
}
