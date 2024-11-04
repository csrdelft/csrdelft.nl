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
#[ORM\Entity(repositoryClass: \CsrDelft\repository\GroepLidRepository::class)]
#[ORM\Table('groep_lid')]
#[ORM\Index(name: 'lid_sinds', columns: ['lid_sinds'])]
class GroepLid
{


	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $groepId;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'vue'])]
	#[ORM\Column(type: 'uid')]
	#[ORM\Id]
	public $uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $profiel;
	/**
	 * CommissieFunctie of opmerking bij lidmaatschap
	 * @var CommissieFunctie|string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string', nullable: true)]
	public $opmerking;
	/**
	 * @var GroepKeuzeSelectie[]
	 */
	#[Serializer\Groups('vue')]
	#[ORM\Column(type: 'groepkeuzeselectie', nullable: true)]
	public $opmerking2;
	/**
	 * Datum en tijd van aanmelden
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups('datatable')]
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
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'door_uid', referencedColumnName: 'uid')]
	public $doorProfiel;
	/**
	 * @var Groep
	 */
	#[ORM\ManyToOne(targetEntity: \Groep::class, inversedBy: 'leden')]
	#[ORM\JoinColumn(name: 'groep_id', referencedColumnName: 'id')]
	public $groep;

	/**
	 * @return string
	 */
	#[Serializer\Groups('vue')]
	public function getNaam()
	{
		return $this->profiel->getNaam();
	}
}
