<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\ProfielRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeTaak.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een crv_taak instantie beschrijft een taak die een lid moet uitvoeren of (niet) uitgevoerd heeft als volgt:
 *  - uniek identificatienummer
 *  - welke functie deze taak inhoud (bijv. tafelpraeses)
 *  - welk lid deze taak uitvoerd
 *  - maaltijd waarmee deze taak verband houdt (optioneel)
 *  - datum en tijd waarop deze taak wordt uitgevoerd
 *  - aantal punten dat verdient kan worden
 *  - extra punten: bonus (positief) of malus (negatief) punten aantal
 *  - aantal punten dat is toegekend (exclusief bonus/malus)
 *  - aantal bonuspunten dat is toegekend
 *  - moment wanneer de punten zijn toegekend (datum en tijd)
 *  - of er een controle van de taak heeft plaatsgevonden (door de hyco) en zo ja of het ok was (anders null)
 *
 * Het aanmaken van een corveetaak kan vanuit CorveeRepetitie gebeuren, maar ook vanuit MaaltijdCorvee bij het indelen van leden voor corvee-functies bij maaltijden; beide in verband met corvee-voorkeuren van leden, gewone danwel maaltijd-gerelateerde corvee-functies. (join Maaltijd.repetitie_id === MaaltijdCorvee.maaltijd_repetitie_id && join MaaltijdCorvee.corvee_repetitie_id === CorveeRepetitie.id)
 * De totale hoeveelheid punten van een lid zijn het puntenaantal van voorgaande jaren opgeslagen in lid.corvee_punten + de som van de toegekende punten van alle taken van een lid.
 *
 *
 * Zie ook MaaltijdCorvee.class.php
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeTakenRepository")
 * @ORM\Table("crv_taken")
 */
class CorveeTaak implements Agendeerbaar {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $taak_id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $functie_id;
	/**
	 * @var string
	 * @ORM\Column(type="uid", nullable=true)
	 */
	public $uid;
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $crv_repetitie_id;
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $maaltijd_id;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="date")
	 */
	public $datum;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $punten;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $bonus_malus = 0;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $punten_toegekend = 0;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $bonus_toegekend = 0;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $wanneer_toegekend;
	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	public $wanneer_gemaild;
	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	public $verwijderd = false;

	public function getPuntenPrognose() {
		return $this->punten + $this->bonus_malus - $this->punten_toegekend - $this->bonus_toegekend;
	}

	public function getLaatstGemaildDate() {
		$pos = strpos($this->wanneer_gemaild, '&#013;');
		if ($pos === false) {
			return null;
		}
		return date_create_immutable(substr($this->wanneer_gemaild, 0, $pos));
	}

	/**
	 * Berekent hoevaak er gemaild is op basis van wanneer er gemaild is.
	 *
	 * @return int
	 */
	public function getAantalKeerGemaild() {
		return substr_count($this->wanneer_gemaild, '&#013;');
	}

	/**
	 * Bepaalt of er een herinnering gemaild moet worden op basis van het aantal verstuurde herinneringen en de ingestelde periode vooraf.
	 *
	 * @return boolean
	 */
	public function getMoetHerinneren() {
		$aantal = $this->getAantalKeerGemaild();
		$datum = $this->datum;
		$laatst = $this->getLaatstGemaildDate();
		$nu = date_create_immutable();

		if ($laatst === $nu) {
			return false;
		}

		for ($i = intval(instelling('corvee', 'herinnering_aantal_mails')); $i > 0; $i--) {

			$herinnering_email_uiterlijk = DateInterval::createFromDateString(instelling('corvee', 'herinnering_' . $i . 'e_mail_uiterlijk'));
			$herinnering_email = DateInterval::createFromDateString(instelling('corvee', 'herinnering_' . $i . 'e_mail'));
			if ($aantal < $i &&
				$nu >= $datum->add($herinnering_email) &&
				$nu <= $datum->add($herinnering_email_uiterlijk)
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Bepaalt of er op tijd is gemaild op basis van de laatst verstuurde email.
	 *
	 * @return boolean
	 */
	public function getIsTelaatGemaild() {
		$aantal = $this->getAantalKeerGemaild();
		$datum = $this->datum;
		$laatst = $this->getLaatstGemaildDate();
		$nu = date_create_immutable();
		$moeten = 0;

		for ($i = intval(instelling('corvee', 'herinnering_aantal_mails')); $i > 0; $i--) {
			$uiterlijk = $datum->add(DateInterval::createFromDateString(instelling('corvee', 'herinnering_' . $i . 'e_mail_uiterlijk')));
			if ($nu >= $uiterlijk) {
				$moeten++;
			}
			if ($aantal <= $i && $laatst >= $uiterlijk) {
				return true;
			}
		}
		if ($moeten > $aantal) {
			return true;
		}
		return false;
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return CorveeFunctie
	 */
	public function getCorveeFunctie() {
		return ContainerFacade::getContainer()->get(CorveeFunctiesRepository::class)->get($this->functie_id);
	}

	public function setUid($uid) {
		if ($uid !== null && !ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException('Geen lid: set lid id');
		}
		$this->uid = $uid;
	}

	public function setWanneerGemaild($datumtijd) {
		if (!is_string($datumtijd)) {
			throw new CsrGebruikerException('Geen string: wanneer gemaild');
		}
		if ($datumtijd !== '') {
			$datumtijd .= '&#013;' . $this->wanneer_gemaild;
		}
		$this->wanneer_gemaild = $datumtijd;
	}

	// Agendeerbaar ############################################################

	public function getUUID() {
		return $this->taak_id . '@corveetaak.csrdelft.nl';
	}

	public function getBeginMoment() {
		return $this->datum->getTimestamp();
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 7200;
	}

	public function getTitel() {
		if ($this->uid) {
			return $this->getCorveeFunctie()->naam . ' ' . ProfielRepository::getNaam($this->uid, 'civitas');
		}
		return 'Corvee vacature (' . $this->getCorveeFunctie()->naam . ')';
	}

	public function getBeschrijving() {
		if ($this->uid) {
			return $this->getCorveeFunctie()->naam;
		}
		return 'Nog niet ingedeeld';
	}

	public function getLocatie() {
		return 'C.S.R. Delft';
	}

	public function getUrl() {
		return '/corvee/rooster';
	}

	public function isHeledag() {
		return true;
	}

	public function isTransparant() {
		return true;
	}


}
