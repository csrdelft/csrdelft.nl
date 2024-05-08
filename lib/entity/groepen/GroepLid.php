<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Util\ReflectionUtil;
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
 */
#[ORM\Table('groep_lid')]
#[ORM\Index(name: 'lid_sinds', columns: ['lid_sinds'])]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\GroepLidRepository::class)]
class GroepLid
{
	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("UUID")
	 */
	public function getUUID()
	{
		return $this->groepId .
			'.' .
			$this->uid .
			'@' .
			strtolower(ReflectionUtil::short_class($this)) .
			'.csrdelft.nl';
	}

	/**
  * Shared primary key
  * Foreign key
  * @var int
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 public $groepId;
	/**
  * Lidnummer
  * Shared primary key
  * Foreign key
  * @var string
  * @Serializer\Groups({"datatable", "vue"})
  */
 #[ORM\Column(type: 'uid')]
 #[ORM\Id]
 public $uid;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
 public $profiel;
	/**
  * CommissieFunctie of opmerking bij lidmaatschap
  * @var CommissieFunctie|string
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $opmerking;
	/**
  * @var GroepKeuzeSelectie[]
  * @Serializer\Groups("vue")
  */
 #[ORM\Column(type: 'groepkeuzeselectie', nullable: true)]
 public $opmerking2;
	/**
  * Datum en tijd van aanmelden
  * @var DateTimeImmutable
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'datetime')]
 public $lidSinds;
	/**
  * Lidnummer van aanmelder
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 public $doorUid;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'door_uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
 public $doorProfiel;
	/**
  * @var Groep
  */
 #[ORM\JoinColumn(name: 'groep_id', referencedColumnName: 'id')]
 #[ORM\ManyToOne(targetEntity: \Groep::class, inversedBy: 'leden')]
 public $groep;

	/**
	 * @return string|null
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("lid")
	 */
	public function getDatatableLid(): ?string
	{
		return ProfielRepository::getLink($this->uid);
	}

	/**
	 * @return string|null
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("door_uid")
	 */
	public function getDatatableDoorUid()
	{
		return $this->doorProfiel->getLink();
	}

	/**
	 * @return string|null
	 * @Serializer\Groups("vue")
	 */
	public function getLink()
	{
		return $this->profiel->getLink();
	}

	/**
	 * @return string
	 * @Serializer\Groups("vue")
	 */
	public function getNaam(): string
	{
		return $this->profiel->getNaam();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("opmerking2")
	 */
	public function getOpmerking2String(): string
	{
		if (is_array($this->opmerking2)) {
			return implode(
				', ',
				array_map(function ($el) {
					return $el->__toString();
				}, $this->opmerking2)
			);
		} else {
			return '';
		}
	}

	public function setProfiel(Profiel $profiel): void
	{
		$this->profiel = $profiel;
		$this->uid = $profiel->uid;
	}
}
