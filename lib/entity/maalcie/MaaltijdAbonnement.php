<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * MaaltijdAbonnement  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een mlt_abonnement instantie beschrijft een individueel abonnement van een lid voor een maaltijd-repetitie als volgt:
 *  - voor welke maaltijd-repetitie deze aanmelding is
 *  - van welk lid dit abonnement is
 *
 *
 * Bij het aanmaken van een abonnement (inschakelen) wordt het lid aangemeldt voor alle maaltijden waar dit abonnement voor geldt, die niet gesloten of verwijderd zijn. Daarom bevat maaltijd een foreign key mlt_repetitie.id.
 * Een abonnement wordt verwijderd als deze wordt uitgeschakeld. Het lid wordt dan afgemeld voor alle maaltijden waar dit abonnement voor geldt die niet gesloten of verwijderd zijn. Daarom bevat maaltijd-aanmelding een foreign key mlt_repetitie.id. Deze verwijzing is redundant, want dat kan ook uitgevonden worden via een join van aanmeldingen met de tabel maaltijden die ook een foreign key mlt_repetitie.id bevat, maar is wel erg handig.
 *
 * Bijvoorbeeld:
 * Gebruiker heeft geen abonnement op donderdag-maaltijden, dus bij het aanmaken van een donderdag-maaltijd wordt de gebruiker niet autmatisch aangemeld.
 * Gebruiker meldt zich handmatig aan voor een specifieke donderdag-maaltijd.
 * Gebruiker schakelt abonnement in voor donderdag-maaltijden. Nu wordt de gebruiker voor alle bestaande (niet-gesloten) donderdag-maaltijden aangemeld.
 * De aanmelding bestaat al, dus wordt niet overschreven en het veld "door_abo" blijft dus NULL.
 * Gebruiker schakelt abonnement weer uit. Nu wordt de gebruiker voor alle bestaande (niet-gesloten) donderdag-maaltijden afgemeld waarvoor de gebruiker automatisch was aangemeld.
 * De handmatige aanmelding blijft dus bestaan en de gebruiker is nog steeds aangemeld voor die ene donderdag-maaltijd.
 * Dit is by design, als de handmatige aanmelding ook verwijderd moet worden bij het uitschakelen van het abonnement is dat een andere design mogelijkheid.
 * (Extreem eenvoudig aan te passen door bij het verwijderen van aanmeldingen niet te checken op door_abonnement.)
 *
 *
 * @see MaaltijdAanmelding
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository::class
	)
]
#[ORM\Table('mlt_abonnementen')]
class MaaltijdAbonnement
{
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $mlt_repetitie_id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	#[ORM\Id]
	public $uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $profiel;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime_immutable')]
	public $wanneer_ingeschakeld; # datetime
	/**
	 * @var MaaltijdRepetitie
	 */
	#[
		ORM\ManyToOne(
			targetEntity: \MaaltijdRepetitie::class,
			inversedBy: 'abonnementen'
		)
	]
	#[
		ORM\JoinColumn(
			name: 'mlt_repetitie_id',
			referencedColumnName: 'mlt_repetitie_id'
		)
	]
	public $maaltijd_repetitie;
	public $van_uid;
	public $waarschuwing;
	public $foutmelding;

	public function getMaaltijdRepetitie(): MaaltijdRepetitie
	{
		return $this->maaltijd_repetitie;
	}

	public function setMaaltijdRepetitie(MaaltijdRepetitie $maaltijdRepetitie)
	{
		$this->maaltijd_repetitie = $maaltijdRepetitie;
		$this->mlt_repetitie_id = $maaltijdRepetitie->getId();
	}

	public function getProfiel(): Profiel
	{
		return $this->profiel;
	}

	public function setProfiel(Profiel $profiel)
	{
		$this->profiel = $profiel;
		$this->uid = $profiel->getId();
	}

	public function setWanneerIngeschakeld(DateTimeImmutable $wanneerIngeschakeld)
	{
		$this->wanneer_ingeschakeld = $wanneerIngeschakeld;
	}

	public function getWanneerIngeschakeld(): DateTimeImmutable
	{
		return $this->wanneer_ingeschakeld;
	}
}
