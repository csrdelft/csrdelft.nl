<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\Orm\Entity\T;
use CsrDelft\repository\ProfielRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use function common\short_class;
use Symfony\Component\Serializer\Annotation as Serializer;


/**
 * AbstractGroepLid.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een groep.
 *
 * @ORM\MappedSuperclass()
 */
abstract class AbstractGroepLid implements DataTableEntry {

	public function getUUID() {
		return $this->groep_id . '.' . $this->uid . '@' . strtolower(short_class($this)) . '.csrdelft.nl';
	}
	protected static $computed_attributes = [
		'link' => [T::String],
	];
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @Serializer\Groups("datatable")
	 */
	public $groep_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 * @Serializer\Groups("datatable")
	 */
	public $uid;
	/**
	 * CommissieFunctie of opmerking bij lidmaatschap
	 * @var CommissieFunctie
	 * @ORM\Column(type="string", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $opmerking;
	/**
	 * @var GroepKeuzeSelectie[]
	 * @ORM\Column(type="groepkeuzeselectie", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $opmerking2;
	/**
	 * Datum en tijd van aanmelden
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("datatable")
	 */
	public $lid_sinds;
	/**
	 * Lidnummer van aanmelder
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $door_uid;

	/**
	 * @return string|null
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("lid")
	 */
	public function getDatatableLid() {
		return ProfielRepository::getLink($this->uid);
	}

	/**
	 * @return string|null
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("door_uid")
	 */
	public function getDatatableDoorUid() {
		return ProfielRepository::getLink($this->door_uid);
	}

	public function getLink() {
		return ProfielRepository::getLink($this->uid);
	}

	/**
	 * @return AbstractGroep
	 */
	abstract public function getGroep();
}
