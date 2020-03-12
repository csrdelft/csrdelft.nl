<?php

namespace CsrDelft\entity\peilingen;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\peilingen\PeilingStemmenRepository")
 * @ORM\Table("peiling_stemmen")
 */
class PeilingStem {
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
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $aantal;
	/**
	 * @var Peiling
	 * @ORM\ManyToOne(targetEntity="Peiling", inversedBy="stemmen")
	 */
	public $peiling;
}
