<?php

namespace CsrDelft\entity;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 *
 * Een LidToestemming beschrijft een Instelling per Lid.
 */
#[Entity(repositoryClass: LidToestemmingRepository::class)]
#[Cache(usage: 'NONSTRICT_READ_WRITE')]
#[Table('lidtoestemmingen')]
#[UniqueConstraint(
	name: 'uid_module_instelling',
	columns: ['uid', 'module', 'instelling']
)]
class LidToestemming
{
	#[Column(type: 'integer'), Id, GeneratedValue]
	public int $id;
	/**
	 * @var string
	 */
	#[Column(type: 'string')]
	public string $module;
	#[Column(type: 'string')]
	public string $instelling;
	#[Column(type: 'text')]
	public string $waarde;
	/**
	 * @var Profiel
	 */
	#[
		ManyToOne(
			targetEntity: Profiel::class,
			inversedBy: 'toestemmingen'
		)
	]
	#[JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
	public Profiel $profiel;

	public function uid(): string
	{
		return $this->profiel->uid;
	}
}
