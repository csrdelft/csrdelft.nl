<?php

namespace CsrDelft\entity;

use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 *
 * Een LidToestemming beschrijft een Instelling per Lid.
 */
#[ORM\Entity(repositoryClass: LidToestemmingRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('lidtoestemmingen')]
#[
	ORM\UniqueConstraint(
		name: 'uid_module_instelling',
		columns: ['uid', 'module', 'instelling']
	)
]
class LidToestemming
{
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
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
	#[ORM\ManyToOne(targetEntity: Profiel::class, inversedBy: 'toestemmingen')]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
	public $profiel;

	public function uid()
	{
		return $this->profiel->uid;
	}
}
