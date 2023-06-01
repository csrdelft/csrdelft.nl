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
 * @ORM\Table("document", indexes={
 *   @ORM\Index(name="Zoeken", columns={"naam", "filename"}, flags={"fulltext"}),
 *   @ORM\Index(name="toegevoegd", columns={"toegevoegd"})
 * })
 * @ORM\Entity(repositoryClass="CsrDelft\repository\turf\TurfGroepRepository")
 */
class TurfGroep
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

}
