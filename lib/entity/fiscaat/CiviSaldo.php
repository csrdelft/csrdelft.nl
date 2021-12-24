<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\formulier\DisplayEntity;
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
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\fiscaat\CiviSaldoRepository"))
 */
class CiviSaldo implements DataTableEntry, DisplayEntity {
	/**
	 * Let op, dit is geen fk naar Profiel. Er zijn CiviSaldo's die geen profiel zijn en vice versa.
	 *
	 * @var string
	 * @ORM\Column(type="uid", unique=true)
	 * @ORM\Id()
	 * @Serializer\Groups({"log", "datatable", "bar"})
	 */
	public $uid;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 * @Serializer\Groups({"log", "datatable", "bar"})
	 */
	public $naam;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups({"log", "datatable", "bar"})
	 */
	public $saldo;
	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
	 * @Serializer\Groups({"log", "datatable"})
	 */
	public $laatst_veranderd;
	/**
	 * @var bool
	 * @ORM\Column(type="boolean", options={"default"=false})
	 * @Serializer\Groups({"log", "datatable", "bar"})
	 */
	public $deleted = false;


	/**
	 * @var CiviBestelling[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="CiviBestelling", mappedBy="civiSaldo")
	 */
	public $bestellingen;

	/**
	 * @return integer
	 * @Serializer\Groups("bar")
	 */
	public function getRecent() {
		$eb = Criteria::expr();
		$criteria = Criteria::create()
			->where($eb->eq('deleted', false))
			->andWhere($eb->gt('moment', date_create_immutable()->add(\DateInterval::createFromDateString('-100 days'))))
		;

		return $this->bestellingen->matching($criteria)->count();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("lichting")
	 */
	public function getDataTableLichting() {
		return substr($this->uid, 0, 2);
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("naam")
	 */
	public function getDataTableNaam() {
		return $this->getWeergave();
	}

	public function getId() {
		return $this->uid;
	}

	/**
	 * @return string
	 * @Serializer\Groups("bar")
	 */
	public function getWeergave(): string {
		return ProfielRepository::existsUid($this->uid) ? ProfielRepository::getNaam($this->uid, 'volledig') : $this->naam;
	}

	public function getLink(): string {
		return ProfielRepository::existsUid($this->uid) ? ProfielRepository::getLink($this->uid, 'volledig') : $this->naam;
	}
}
