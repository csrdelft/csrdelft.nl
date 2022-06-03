<?php

namespace CsrDelft\entity\peilingen;

use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\peilingen\PeilingStemmenRepository")
 * @ORM\Table("peiling_stemmen")
 */
class PeilingStem
{
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $peiling_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;
	/**
	 * @var int
	 * @ORM\Column(type="integer", options={"default" = "1"})
	 */
	public $aantal;
	/**
	 * @var Peiling
	 * @ORM\ManyToOne(targetEntity="Peiling", inversedBy="stemmen")
	 */
	public $peiling;
}
