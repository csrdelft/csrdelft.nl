<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

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
 */
class CorveeTaak extends PersistentEntity implements Agendeerbaar {
	# primary key

	public $taak_id; # int 11
	public $functie_id; # foreign key crv_functie.id
	public $uid; # foreign key lid.uid
	public $crv_repetitie_id; # foreign key crv_repetitie.id
	public $maaltijd_id; # foreign key maaltijd.id
	public $datum; # date
	public $punten; # int 11
	public $bonus_malus = 0; # int 11
	public $punten_toegekend = 0; # int 11
	public $bonus_toegekend = 0; # int 11
	public $wanneer_toegekend; # datetime
	public $wanneer_gemaild; # text
	public $verwijderd = false; # boolean

	public function getPuntenPrognose() {
		return $this->punten + $this->bonus_malus - $this->punten_toegekend - $this->bonus_toegekend;
	}

	public function getLaatstGemaildTimestamp() {
		$pos = strpos($this->wanneer_gemaild, '&#013;');
		if ($pos === false) {
			return null;
		}
		return strtotime(substr($this->wanneer_gemaild, 0, $pos));
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
		$datum = strtotime($this->datum);
		$laatst = $this->getLaatstGemaildTimestamp();
		$nu = strtotime(date('Y-m-d'));

		if ($laatst === $nu) {
			return false;
		}

		for ($i = intval(instelling('corvee', 'herinnering_aantal_mails')); $i > 0; $i--) {

			if ($aantal < $i &&
				$nu >= strtotime(instelling('corvee', 'herinnering_' . $i . 'e_mail'), $datum) &&
				$nu <= strtotime(instelling('corvee', 'herinnering_' . $i . 'e_mail_uiterlijk'), $datum)
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
		$datum = strtotime($this->datum);
		$laatst = $this->getLaatstGemaildTimestamp();
		$nu = strtotime(date('Y-m-d'));
		$moeten = 0;

		for ($i = intval(instelling('corvee', 'herinnering_aantal_mails')); $i > 0; $i--) {
			$uiterlijk = strtotime(instelling('corvee', 'herinnering_' . $i . 'e_mail_uiterlijk'), $datum);
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
		return FunctiesModel::get($this->functie_id);
	}

	public function setUid($uid) {
		if ($uid !== null && !ProfielModel::existsUid($uid)) {
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
		return strtotime($this->datum);
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 7200;
	}

	public function getTitel() {
		if ($this->uid) {
			return $this->getCorveeFunctie()->naam . ' ' . ProfielModel::getNaam($this->uid, 'civitas');
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
		return '/corveerooster';
	}

	public function isHeledag() {
		return true;
	}

	public function isTransparant() {
		return true;
	}

	protected static $table_name = 'crv_taken';
	protected static $persistent_attributes = array(
		'taak_id' => array(T::Integer, false, 'auto_increment'),
		'functie_id' => array(T::Integer),
		'uid' => array(T::UID, true),
		'crv_repetitie_id' => array(T::Integer, true),
		'maaltijd_id' => array(T::Integer, true),
		'datum' => array(T::Date),
		'punten' => array(T::Integer),
		'bonus_malus' => array(T::Integer),
		'punten_toegekend' => array(T::Integer),
		'bonus_toegekend' => array(T::Integer),
		'wanneer_toegekend' => array(T::DateTime, true),
		'wanneer_gemaild' => array(T::Text, true),
		'verwijderd' => array(T::Boolean)
	);

	protected static $primary_key = array('taak_id');

}
