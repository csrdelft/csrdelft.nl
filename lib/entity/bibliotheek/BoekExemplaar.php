<?php

namespace CsrDelft\entity\bibliotheek;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\model\entity\bibliotheek
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\bibliotheek\BoekExemplaarRepository::class
	)
]
#[ORM\Table('biebexemplaar')]
class BoekExemplaar
{
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer', options: ['default' => 0])]
	public $boek_id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	public $eigenaar_uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'eigenaar_uid', referencedColumnName: 'uid')]
	public $eigenaar;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $opmerking;

	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid', nullable: true)]
	public $uitgeleend_uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uitgeleend_uid', referencedColumnName: 'uid')]
	public $uitgeleend;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $toegevoegd;
	/**
	 * @var BoekExemplaarStatus
	 */
	#[ORM\Column(type: 'enumBoekExemplaarStatus')]
	public $status;
	/**
	 * @var DateTimeImmutable|null
	 */
	#[ORM\Column(type: 'datetime', nullable: true)]
	public $uitleendatum;
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer', options: ['default' => 0])]
	public $leningen;

	/**
	 * @var Boek
	 */
	#[ORM\ManyToOne(targetEntity: \Boek::class, inversedBy: 'exemplaren')]
	public $boek;

	public function isBiebBoek(): bool
	{
		return $this->eigenaar_uid == 'x222';
	}
}
