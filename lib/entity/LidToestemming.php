<?php

namespace CsrDelft\entity;

use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 *
 * Een LidToestemming beschrijft een Instelling per Lid.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\instellingen\LidToestemmingRepository")
 * @ORM\Table("lidtoestemmingen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class LidToestemming {
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $module;
	/**
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $waarde;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel", inversedBy="toestemmingen")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;
}
