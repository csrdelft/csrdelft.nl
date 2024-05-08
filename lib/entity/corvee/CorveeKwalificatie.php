<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeKwalificatie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een CorveeKwalificatie instantie geeft aan dat een lid gekwalificeerd is voor een functie en sinds wanneer.
 * Dit is benodigd voor sommige CorveeFuncties zoals kwalikok.
 *
 * Zie ook CorveeFunctie.class.php
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeKwalificatiesRepository")
 * @ORM\Table("crv_kwalificaties")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class CorveeKwalificatie
{
	/**
	 * Lidnummer
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $functie_id;
	/**
	 * Datum en tijd
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $wanneer_toegewezen;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;

	/**
	 * @var CorveeFunctie
	 * @ORM\ManyToOne(targetEntity="CorveeFunctie", inversedBy="kwalificaties")
	 * @ORM\JoinColumn(name="functie_id", referencedColumnName="functie_id")
	 */
	public $corveeFunctie;

	public function setCorveeFunctie(CorveeFunctie $corveeFunctie = null): void
	{
		$this->corveeFunctie = $corveeFunctie;
		$this->functie_id = $corveeFunctie->functie_id ?? null;
	}

	public function setProfiel(Profiel $profiel = null): void
	{
		$this->profiel = $profiel;
		$this->uid = $profiel->uid ?? null;
	}
}
