<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * MaaltijdAanmelding.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een mlt_aanmelding instantie beschrijft een individuele aanmelding van een lid voor een maaltijd als volgt:
 *  - voor welke maaltijd deze aanmelding is
 *  - van welk lid deze aanmelding is
 *  - het aantal gasten dat het lid aanmeldt
 *  - opmerkingen met betrekking tot de aangemelde gasten (bijv. allergien)
 *  - of de aanmelding door een abonnement is aangemaakt en zo ja voor welke maaltijd-repetitie
 *  - door welk lid deze aanmelding is gemaakt (bijv. als een lid door een ander lid wordt aangemeld, of door de fiscus achteraf, anders gelijk aan aanmelding lid id)
 *  - wanneer de aanmelding voor het laatst is aangepast
 *
 * Een aanmelding wordt verwijderd als een lid zich afmeldt of het abonnement uitschakelt dat deze aanmelding heeft aangemaakt, BEHOUDENS gesloten maaltijden.
 * Een aanmelding blijft verder altijd bestaan, zelfs als de maaltijd wordt aangemerkt als verwijderd. Dus ook als de aanmelding NIET door een abonnement is gemaakt en het abonnement voor deze maaltijd-repetitie uitgeschakeld wordt.
 * Een lid wordt automatisch aangemeld bij het creeren van een repetitie-maaltijd als er een abonnement op die maaltijd-repetie is ingesteld voor dat lid.
 * Het is mogelijk dat door de fiscus een aanmelding wordt aangemaakt (of verwijderd), zelfs na het sluiten van de maaltijd.
 *
 *
 * Zie ook MaaltijdAbonnement.class.php
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository")
 * @ORM\Table("mlt_aanmeldingen")
 */
class MaaltijdAanmelding {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $maaltijd_id;
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $aantal_gasten = 0;
	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $gasten_eetwens;
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $door_abonnement;
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $door_uid;
	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $laatst_gewijzigd;
	/**
	 * @var Maaltijd
	 * @ORM\ManyToOne(targetEntity="Maaltijd", inversedBy="aanmeldingen")
	 * @ORM\JoinColumn(name="maaltijd_id", referencedColumnName="maaltijd_id")
	 */
	public $maaltijd;

	/**
	 * Haal het MaalCie saldo op van het lid van deze aanmelding.
	 *
	 * @return float if lid exists, false otherwise
	 */
	public function getSaldo() {
		return ProfielRepository::get($this->uid)->getCiviSaldo();
	}

	/**
	 * Bereken of het saldo toereikend is voor de prijs van de maaltijd.
	 *
	 * 3: saldo meer dan genoeg
	 *
	 * 2: saldo precies genoeg
	 *
	 * 1: saldo positief maar te weinig
	 *
	 * 0: saldo nul
	 *
	 * -1: saldo negatief
	 *
	 * @return int
	 */
	public function getSaldoStatus() {
		$saldo = $this->getSaldo();
		$prijs = $this->maaltijd->getPrijsFloat();

		if ($saldo > $prijs) { // saldo meer dan genoeg
			return 3;
		} elseif ($saldo > $prijs - 0.004) { // saldo precies genoeg
			return 2;
		} elseif ($saldo > 0.004) { // saldo positief maar te weinig
			return 1;
		} elseif ($saldo > -0.004) { // saldo nul
			return 0;
		} else {
			return -1; // saldo negatief
		}
	}

	/**
	 * Melding voor saldo status.
	 *
	 * @return String
	 */
	public function getSaldoMelding() {
		$status = $this->getSaldoStatus();
		$prijs = sprintf('%.2f', $this->maaltijd->getPrijsFloat());
		switch ($status) {
			case 3:
				return 'ok';
			case 2:
				return $prijs;
			case 1:
				return '&lt; ' . $prijs;
			case 0:
				return '0';
			case -1:
				return '&lt; 0';
		}

		throw new CsrException("Ongeldige saldo status: " . $status);
	}
}
