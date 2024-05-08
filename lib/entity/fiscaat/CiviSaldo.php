<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\formulier\DisplayEntity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * CiviSaldo.class.php
 *
 * Bewaart het saldo van een lid, uid is een verwijzing naar account.
 *
 * Uid kan ook een niet bestaande uid bevatten voor profielen die niet kunnen inloggen en alleen via SocCie kunnen
 * afrekenen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/04/2017
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\fiscaat\CiviSaldoRepository::class)]
class CiviSaldo implements DataTableEntry, DisplayEntity
{
	/**
  * Let op, dit is geen fk naar Profiel. Er zijn CiviSaldo's die geen profiel zijn en vice versa.
  *
  * @var string
  * @Serializer\Groups({"log", "datatable", "bar"})
  */
 #[ORM\Column(type: 'uid', unique: true)]
 #[ORM\Id]
 public $uid;
	/**
  * @var string
  * @Serializer\Groups({"log", "datatable", "bar"})
  */
 #[ORM\Column(type: 'text')]
 public $naam;
	/**
  * @var integer
  * @Serializer\Groups({"log", "datatable", "bar"})
  */
 #[ORM\Column(type: 'integer')]
 public $saldo;
	/**
  * @var \DateTimeImmutable
  * @Serializer\Groups({"log", "datatable"})
  */
 #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
 public $laatst_veranderd;
	/**
  * @var bool
  * @Serializer\Groups({"log", "datatable", "bar"})
  */
 #[ORM\Column(type: 'boolean', options: ['default' => false])]
 public $deleted = false;

	/**
  * @var CiviBestelling[]|ArrayCollection
  */
 #[ORM\OneToMany(targetEntity: \CiviBestelling::class, mappedBy: 'civiSaldo')]
 public $bestellingen;

	/**
	 * @return integer
	 * @Serializer\Groups("bar")
	 */
	public function getRecent(): int
	{
		$eb = Criteria::expr();
		$criteria = Criteria::create()
			->where($eb->eq('deleted', false))
			->andWhere(
				$eb->gt(
					'moment',
					date_create_immutable()->add(
						\DateInterval::createFromDateString('-100 days')
					)
				)
			);

		return $this->bestellingen->matching($criteria)->count();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("lichting")
	 */
	public function getDataTableLichting()
	{
		return substr($this->uid, 0, 2);
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("naam")
	 */
	public function getDataTableNaam(): string
	{
		return $this->getWeergave();
	}

	public function getId(): string
	{
		return $this->uid;
	}

	/**
	 * @return string
	 * @Serializer\Groups("bar")
	 */
	public function getWeergave(): string
	{
		return ProfielRepository::existsUid($this->uid)
			? ProfielRepository::getNaam($this->uid, 'volledig')
			: $this->naam;
	}

	public function getLink(): string
	{
		return ProfielRepository::existsUid($this->uid)
			? ProfielRepository::getLink($this->uid, 'volledig')
			: $this->naam;
	}
}
