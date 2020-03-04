<?php

namespace CsrDelft\entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Een instelling instantie beschrijft een key-value pair voor een module.
 *
 * Bijvoorbeeld:
 *
 * Voor maaltijden-module:
 *  - Standaard maaltijdprijs
 *  - Marge in verband met gasten
 *
 * Voor corvee-module:
 *  - Corveepunten per jaar
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\instellingen\InstellingenRepository")
 * @ORM\Table("instellingen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Instelling {

	/**
	 * Shared primary key
	 * @var string
	 * @ORM\Column(type="string", length=191)
	 * @ORM\Id()
	 */
	public $module;
	/**
	 * Shared primary key
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
