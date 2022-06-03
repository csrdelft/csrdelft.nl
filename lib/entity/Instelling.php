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
 * @ORM\Table(
 *   "instellingen",
 *   uniqueConstraints={@ORM\UniqueConstraint(name="module_instelling", columns={"module", "instelling"})}
 * )
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Instelling {
	/**
	 * @var integer
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $module;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $instelling;
	/**
	 * Value
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $waarde;
}
