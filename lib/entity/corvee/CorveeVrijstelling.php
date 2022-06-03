<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeVrijstelling  |  P.W.G. Brussee (brussee@live.nl)
 *
 * Een crv_vrijstelling instantie bevat het volgende per lid:
 *  - begindatum van de periode waarvoor de vrijstelling geldt
 *  - einddatum van de periode waarvoor de vrijstelling geldt
 *  - percentage van de corveepunten die in een jaar gehaald dienen te worden
 *
 * Wordt gebruikt bij de indeling van corveetaken om bijv. leden die
 * in het buitenland zitten niet in te delen gedurende die periode.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeVrijstellingenRepository")
 * @ORM\Table("crv_vrijstellingen")
 */
class CorveeVrijstelling
{
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $begin_datum;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $eind_datum;
	// TODO: Check percentage tussen 0 en 100 in controller
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $percentage;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;

	public function setProfiel($profiel)
	{
		$this->profiel = $profiel;
		$this->uid = $profiel->uid ?? null;
	}

	public function getPunten()
	{
		return (int)ceil($this->percentage * intval(instelling('corvee', 'punten_per_jaar')) / 100);
	}
}
