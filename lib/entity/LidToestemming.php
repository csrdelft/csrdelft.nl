<?php

namespace CsrDelft\entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 *
 * Een LidToestemming beschrijft een Instelling per Lid.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\instellingen\LidToestemmingRepository")
 * @ORM\Table("lidtoestemmingen")
 */
class LidToestemming {
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var string
	 * @ORM\Column(type="string", length=191)
	 * @ORM\Id()
	 */
	public $module;
	/**
	 * @var string
	 * @ORM\Column(type="string", length=191)
	 * @ORM\Id()
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $waarde;
}
