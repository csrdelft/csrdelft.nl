<?php

namespace CsrDelft\entity\turf;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Bestand;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\Icon;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Goedhart
 * @ORM\Table("TurfGroep"}
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\turf\TurfGroepLidRepository")
 */
class TurfGroepLid
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $naam;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	public $openbaar;
}
