<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\entity\interfaces\HeeftAanmeldLimiet;
use CsrDelft\model\fiscaat\CiviProductModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Maaltijd.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een mlt_maaltijd instantie beschrijft een individuele maaltijd als volgt:
 *  - uniek identificatienummer
 *  - door welke repetitie deze maaltijd is aangemaakt (optioneel)
 *  - titel (bijv. Donderdagmaaltijd)
 *  - limiet op het aantal aanmeldingen
 *  - datum en tijd waarop de maaltijd plaatsvind (op basis van vandaag en/of repetitie.dag_vd_week en repetitie.periode)
 *  - of de maaltijd gesloten is voor aanmeldingen en afmeldingen
 *  - moment wanneer de maaltijd voor het laatst is gesloten (gebeurt in principe maar 1 keer)
 *  - of de maaltijd verwijderd is (in de prullenbak zit)
 *  - of er restricties gelden voor wie zich mag aanmelden
 *
 * Een gesloten maaltijd kan weer heropend worden.
 * Een verwijderde maaltijd kan weer uit de prullenbak worden gehaald.
 * Zolang een maaltijd verwijderd is doet en telt deze niet meer mee in het maalcie-systeem.
 * Als de restricties gewijzigt worden nadat er al aangemeldingen zijn (direct na het aanmaken van een maaltijd vanwege abonnementen) worden illegale aanmeldingen automatisch verwijderd.
 * In principe worden maaltijden aangemaakt vanuit maaltijd-repetitie in verband met maaltijd-corvee-taken en corvee-voorkeuren van leden.
 *
 *
 * Zie ook MaaltijdAanmelding.class.php
 *
 */
class Maaltijd extends PersistentEntity implements Agendeerbaar, HeeftAanmeldLimiet {
	# primary key

	public $maaltijd_id; # int 11
	public $mlt_repetitie_id; # foreign key mlt_repetitie.id
	public $product_id;
	public $titel; # string 255
	public $aanmeld_limiet; # int 11
	public $datum; # date
	public $tijd; # time
	public $gesloten = false; # boolean
	public $laatst_gesloten; # int 11
	public $verwijderd = false; # boolean
	public $aanmeld_filter; # string 255
	public $omschrijving; # text
	public $aantal_aanmeldingen;
	public $archief;
	public $verwerkt = false;
	/**
	 * De taak die rechten geeft voor het bekijken en sluiten van de maaltijd(-lijst)
	 * @var CorveeTaak
	 */
	public $maaltijdcorvee;

	public function getPrijsFloat() {
		return (float)$this->getPrijs() / 100.0;
	}

	public function getPrijs() {
		return CiviProductModel::instance()->getPrijs(CiviProductModel::instance()->getProduct($this->product_id))->prijs;
	}

	/**
	 * @return int
	 */
	public function getAantalAanmeldingen() {
		$aantal = MaaltijdAanmeldingenModel::instance()->select(array('SUM(aantal_gasten) + COUNT(*)'), 'maaltijd_id = ?', array($this->maaltijd_id));
		return (int)$aantal->fetchColumn();
	}

	/**
	 * Bereken de marge in verband met niet aangemelde gasten.
	 *
	 * @return int
	 */
	public function getMarge() {
		$aantal = $this->getAantalAanmeldingen();
		$marge = floor($aantal / floatval(instelling('maaltijden', 'marge_gasten_verhouding')));
		$min = intval(instelling('maaltijden', 'marge_gasten_min'));
		if ($marge < $min) {
			$marge = $min;
		}
		$max = intval(instelling('maaltijden', 'marge_gasten_max'));
		if ($marge > $max) {
			$marge = $max;
		}
		return $marge;
	}

	/**
	 * Bereken het budget voor deze maaltijd.
	 *
	 * @return double
	 */
	public function getBudget() {
		$budget = $this->getAantalAanmeldingen() + $this->getMarge();
		$budget *= $this->getPrijs() - intval(instelling('maaltijden', 'budget_maalcie'));
		return floatval($budget) / 100.0;
	}

	/**
	 * Vind corveetaken van gegeven functie bij deze maaltijd
	 *
	 * @param $functieID int ID van de functie
	 * @return CorveeTaak[]
	 */
	public function getCorveeTaken($functieID) {
		$gevonden = [];

		/** @var CorveeFunctie[] $functies */
		$functie = FunctiesModel::get($functieID);
        $taken = CorveeTakenModel::instance()->find('functie_id = ? AND maaltijd_id = ? AND verwijderd = 0', [$functie->functie_id, $this->maaltijd_id]);
        foreach ($taken as $taak) {
            $gevonden[] = $taak;
        }

		return $gevonden;
	}

	// Agendeerbaar ############################################################

	public function getTitel() {
		return $this->titel;
	}

	public function getBeginMoment() {
		return strtotime($this->datum . ' ' . $this->tijd);
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 7200;
	}

	public function getBeschrijving() {
		return 'Maaltijd met ' . $this->getAantalAanmeldingen() . ' eters';
	}

	public function getLocatie() {
		return 'C.S.R. Delft';
	}

	public function getUrl() {
		return '/maaltijden';
	}

	public function isHeledag() {
		return false;
	}

	public function isTransparant() {
		// Toon als transparant (vrij) als lid dat wil of lid niet ingeketzt is
		$aangemeld = MaaltijdAanmeldingenModel::instance()->getIsAangemeld($this->maaltijd_id, LoginModel::getUid());
		return lid_instelling('agenda', 'transparantICal') === 'ja' || !$aangemeld;
	}

	// Controller ############################################################

	/**
	 * Deze functie bepaalt of iemand de maaltijd(-lijst) mag zien.
	 *
	 * @param string $uid
	 * @return boolean
	 * @throws CsrException
	 */
	public function magBekijken($uid) {
		if (!isset($this->maaltijdcorvee)) {
			// Zoek op datum, want er kunnen meerdere maaltijden op 1 dag zijn terwijl er maar 1 kookploeg is.
			// Ook hoeft een taak niet per se gekoppeld te zijn aan een maaltijd (maximaal aan 1 maaltijd).
			$taken = CorveeTakenModel::instance()->getTakenVoorAgenda($this->getBeginMoment(), $this->getBeginMoment());
			foreach ($taken as $taak) {
				if ($taak->uid === $uid AND $taak->maaltijd_id !== null) { // checken op gekoppelde maaltijd (zie hierboven)
					$this->maaltijdcorvee = $taak; // de taak die toegang geeft tot de maaltijdlijst
					return true;
				}
			}
			$this->maaltijdcorvee = false;
		}
		return $this->maaltijdcorvee !== false;
	}

	/**
	 * Deze functie bepaalt of iemand deze maaltijd mag sluiten of niet.
	 *
	 * @param string $uid
	 * @return boolean
	 * @throws CsrException
	 */
	public function magSluiten($uid) {
		return $this->magBekijken($uid) AND $this->maaltijdcorvee->getCorveeFunctie()->maaltijden_sluiten; // mag iemand met deze functie maaltijden sluiten?
	}

	protected static $table_name = 'mlt_maaltijden';
	protected static $persistent_attributes = array(
		'maaltijd_id' => array(T::Integer, false, 'auto_increment'),
		'mlt_repetitie_id' => array(T::Integer, true),
		'product_id' => array(T::Integer),
		'titel' => array(T::String),
		'aanmeld_limiet' => array(T::Integer),
		'datum' => array(T::Date),
		'tijd' => array(T::Time),
		'gesloten' => array(T::Boolean),
		'laatst_gesloten' => array(T::Timestamp, true),
		'verwijderd' => array(T::Boolean),
		'aanmeld_filter' => array(T::String, true),
		'omschrijving' => array(T::Text, true),
		'verwerkt' => array(T::Boolean)
	);

	protected static $primary_key = array('maaltijd_id');

	/**
	 * De API voor de app gebruikt json_encode
	 *
	 * @return array|mixed
	 * @throws CsrGebruikerException
	 */
	public function jsonSerialize() {
		$json = parent::jsonSerialize();
		$json['repetitie_naam'] = is_int($this->mlt_repetitie_id) ? MaaltijdRepetitiesModel::instance()->getRepetitie($this->mlt_repetitie_id)->standaard_titel : '';
		$json['tijd'] = date('G:i', strtotime($json['tijd']));
		$json['aantal_aanmeldingen'] = $this->getAantalAanmeldingen();
		$json['prijs'] = strval($this->getPrijs());
		return $json;
	}

	function getAanmeldLimiet() {
		return $this->aanmeld_limiet;
	}
}
