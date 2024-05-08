<?php

namespace CsrDelft\entity\peilingen;

use CsrDelft\repository\peilingen\PeilingStemmenRepository;
use Peiling;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Table('peiling_stemmen')]
#[ORM\Entity(repositoryClass: PeilingStemmenRepository::class)]
class PeilingStem
{
	/**
  * Shared primary key
  * Foreign key
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 public $peiling_id;
	/**
  * Lidnummer
  * Shared primary key
  * Foreign key
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 #[ORM\Id]
 public $uid;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $profiel;
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer', options: ['default' => '1'])]
 public $aantal;
	/**
  * @var Peiling
  */
 #[ORM\ManyToOne(targetEntity: Peiling::class, inversedBy: 'stemmen')]
 public $peiling;
}
