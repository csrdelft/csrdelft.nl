<?php

namespace CsrDelft\entity\commissievoorkeuren;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\AccessService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoorkeurVoorkeur
 * @package CsrDelft\model\entity\commissievoorkeuren
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository::class
	)
]
class VoorkeurVoorkeur
{
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	#[ORM\Id]
	public $uid;

	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $cid;

	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $voorkeur;

	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $timestamp;

	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $profiel;

	/**
	 * @var VoorkeurCommissie
	 */
	#[ORM\ManyToOne(targetEntity: \VoorkeurCommissie::class)]
	#[ORM\JoinColumn(name: 'cid')]
	public $commissie;
}
