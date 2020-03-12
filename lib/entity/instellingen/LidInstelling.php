<?php

namespace CsrDelft\entity\instellingen;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een LidInstelling beschrijft een Instelling per Lid.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\instellingen\LidInstellingenRepository")
 * @ORM\Table("lidinstellingen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class LidInstelling {

	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * Shared primary key
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $module;
	/**
	 * Shared primary key
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $waarde;
}
