<?php


namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\enum\CommissieFunctie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\repository\ProfielRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class GroepLid
 * @package CsrDelft\entity\groepen2
 * @ORM\Entity(repositoryClass="CsrDelft\repository\GroepLidRepository")
 * @ORM\Table("groep_lid", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class GroepLid
{
	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("UUID")
	 */
	public function getUUID() {
		return $this->groepId . '.' . $this->uid . '@' . strtolower(short_class($this)) . '.csrdelft.nl';
	}
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @Serializer\Groups("datatable")
	 */
	public $groepId;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public $uid;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;
	/**
	 * CommissieFunctie of opmerking bij lidmaatschap
	 * @var CommissieFunctie|string
	 * @ORM\Column(type="string", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $opmerking;
	/**
	 * @var GroepKeuzeSelectie[]
	 * @ORM\Column(type="groepkeuzeselectie", nullable=true)
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public $opmerking2;
	/**
	 * Datum en tijd van aanmelden
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("datatable")
	 */
	public $lidSinds;
	/**
	 * Lidnummer van aanmelder
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $doorUid;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="door_uid", referencedColumnName="uid")
	 */
	public $doorProfiel;
	/**
	 * @var Groep
	 * @ORM\ManyToOne(targetEntity="Groep", inversedBy="leden")
	 * @ORM\JoinColumn(name="groep_id", referencedColumnName="id")
	 */
	public $groep;
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
		return $this->doorProfiel->getLink();
	}

	/**
	 * @return string|null
	 * @Serializer\Groups("vue")
	 */
	public function getLink() {
		return $this->profiel->getLink();
	}

	/**
	 * @return string
	 * @Serializer\Groups("vue")
	 */
	public function getNaam() {
		return $this->profiel->getNaam();
	}
}
