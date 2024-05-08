<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeRepetitie.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een crv_repetitie instantie beschrijft een corvee taak die periodiek moet worden uitgevoerd als volgt:
 *  - uniek identificatienummer
 *  - bij welke maaltijdrepetitie deze periodieke taken horen (optioneel)
 *  - op welke dag van de week dit moet gebeuren
 *  - na hoeveel dagen dit opnieuw moet gebeuren
 *  - welke functie deze periodieke taak inhoud (bijv. kwalikok)
 *  - standaard aantal punten dat een lid krijgt voor deze periodieke taak
 *  - standaard aantal mensen dat deze periodieke taak moeten uitvoeren (bijv. 1 kwalikok, 2 hulpkoks, etc.)
 *  - of deze periodieke taak als voorkeur kan worden opgegeven (bijv. kwalikok is niet voorkeurbaar)
 *
 * Bij het koppelen van corvee-repetities aan een maaltijd-repetitie maakt het mogelijk om bij het aanmaken van
 * een maaltijd automatisch ook corveetaken aan te maken.
 * Deze klasse weet dus welke en hoeveel corvee-functies er bij welke maaltijd-repetitie horen,
 * in verband met het later toewijzen van corvee-functies als taak aan een of meerdere leden.
 * Een maaltijd die los wordt aangemaakt, dus niet vanuit een maaltijd-repetitie, krijgt dus geen standaard corvee-taken.
 * Deze zullen met de hand moeten worden toegevoegd. Daarbij kan gebruik gemaakt worden van de dag van de week
 * van de maaltijd en te kijken naar de dag van de week van corvee-repetities.
 * Een lid kan een voorkeur aangeven voor een corvee-repetitie.
 *
 *
 * Zie ook CorveeTaak.class.php
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeRepetitiesRepository")
 * @ORM\Table("crv_repetities")
 */
class CorveeRepetitie implements DisplayEntity
{
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $crv_repetitie_id;
	/**
	 * @var integer|null
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $mlt_repetitie_id;
	/**
	 * @var MaaltijdRepetitie|null
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\maalcie\MaaltijdRepetitie")
	 * @ORM\JoinColumn(name="mlt_repetitie_id", referencedColumnName="mlt_repetitie_id", nullable=true)
	 */
	public $maaltijdRepetitie;
	/**
	 * 0: zondag
	 * 6: zaterdag
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $dag_vd_week;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $periode_in_dagen;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $functie_id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $standaard_punten;
	/**
	 * @var integer|null
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $standaard_aantal;
	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $voorkeurbaar;

	/**
	 * @var CorveeFunctie
	 * @ORM\ManyToOne(targetEntity="CorveeFunctie")
	 * @ORM\JoinColumn(name="functie_id", referencedColumnName="functie_id")
	 */
	public $corveeFunctie;

	public function getDagVanDeWeekText(): string|false
	{
		$weekDagen = ["zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag"];
		return $weekDagen[$this->dag_vd_week];
	}

	public function getPeriodeInDagenText(): string
	{
		switch ($this->periode_in_dagen) {
			case 0:
				return '-';
			case 1:
				return 'elke dag';
			case 7:
				return 'elke week';
			default:
				if ($this->periode_in_dagen % 7 === 0) {
					return 'elke ' . $this->periode_in_dagen / 7 . ' weken';
				} else {
					return 'elke ' . $this->periode_in_dagen . ' dagen';
				}
		}
	}

	public function getId(): integer
	{
		return $this->crv_repetitie_id;
	}

	public function getWeergave(): string
	{
		if ($this->corveeFunctie) {
			return $this->corveeFunctie->naam .
				' ' .
				$this->getDagVanDeWeekText() .
				' ' .
				$this->getPeriodeInDagenText();
		} else {
			return $this->getDagVanDeWeekText() .
				' ' .
				$this->getPeriodeInDagenText();
		}
	}
}
