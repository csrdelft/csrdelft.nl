<?php

namespace CsrDelft\entity\instellingen;

use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een LidInstelling beschrijft een Instelling per Lid.
 */
#[ORM\Table('lidinstellingen')]
#[ORM\UniqueConstraint(name: 'uid_module_instelling', columns: ['uid', 'module', 'instelling'])]
#[ORM\Entity(repositoryClass: LidInstellingenRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
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
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $profiel;
}
