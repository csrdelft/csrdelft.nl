<?php

namespace CsrDelft\entity\instellingen;

use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een LidInstelling beschrijft een Instelling per Lid.
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\instellingen\LidInstellingenRepository::class
	)
]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('lidinstellingen')]
#[
	ORM\UniqueConstraint(
		name: 'uid_module_instelling',
		columns: ['uid', 'module', 'instelling']
	)
]
class LidInstelling
{
	/**
	 * @var integer
	 */
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $module;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $instelling;
	/**
	 * Value
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $waarde;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
	public $profiel;
}
