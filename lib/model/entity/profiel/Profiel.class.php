<?php

namespace CsrDelft\model\entity\profiel;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\GoogleSync;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\Kring;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\OntvangtContactueel;
use CsrDelft\model\entity\security\Account;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\KringenModel;
use CsrDelft\model\groepen\leden\BestuursLedenModel;
use CsrDelft\model\groepen\leden\CommissieLedenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\bbcode\CsrBB;
use DateTime;
use GuzzleHttp\Exception\RequestException;


/**
 * Profiel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Profiel van een lid. Agendeerbaar vanwege verjaardag in agenda.
 *
 * @property-read Account $account
 * @property-read Kring $kring
 * @property-read string $primary_email
 */
class Profiel extends PersistentEntity implements Agendeerbaar {

	// account
	public $uid;
	/**
	 * @var ProfielLogGroup[] changelog
	 */
	public $changelog;
	// naam
	public $voornamen;
	public $voorletters;
	public $voornaam;
	public $tussenvoegsel;
	public $achternaam;
	public $postfix;
	public $nickname;
	public $duckname;
	// fysiek
	public $geslacht;
	public $gebdatum;
	public $sterfdatum;
	public $lengte;
	// getrouwd
	public $echtgenoot;
	public $adresseringechtpaar;
	public $ontvangtcontactueel;
	// adres
	public $adres;
	public $postcode;
	public $woonplaats;
	public $land;
	public $telefoon;
	public $o_adres;
	public $o_postcode;
	public $o_woonplaats;
	public $o_land;
	public $o_telefoon;
	// contact
	public $email;
	public $mobiel;
	public $linkedin;
	public $website;
	// studie
	public $studie;
	public $studiejaar;
	public $beroep;
	// lidmaatschap
	public $lidjaar;
	public $lidafdatum;
	public $status;
	// geld
	public $bankrekening;
	public $machtiging;
	// verticale
	public $moot;
	public $verticale;
	public $verticaleleider;
	public $kringcoach;
	// civi-gegevens
	public $patroon;
	public $eetwens;
	public $corvee_punten;
	public $corvee_punten_bonus;
	// novitiaat
	public $novitiaat;
	public $novitiaatBijz;
	public $medisch;
	public $startkamp;
	public $matrixPlek;
	public $novietSoort;
	public $kgb;
	public $vrienden;
	public $middelbareSchool;
	// overig
	public $kerk;
	public $muziek;
	public $zingen;
	// lazy loading
	private $kinderen;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		// account
		'uid' => array(T::UID),
		'changelog' => array(T::JSON, false, [ProfielLogGroup::class, ProfielCreateLogGroup::class, ProfielLogVeldenVerwijderChange::class, ProfielLogCoveeTakenVerwijderChange::class, ProfielLogTextEntry::class, ProfielLogValueChangeCensuur::class, ProfielLogValueChange::class, ProfielUpdateLogGroup::class, UnparsedProfielLogGroup::class, DateTime::class]),
		// naam
		'voornamen' => array(T::String, true),
		'voorletters' => array(T::String),
		'voornaam' => array(T::String),
		'tussenvoegsel' => array(T::String, true),
		'achternaam' => array(T::String),
		'postfix' => array(T::String, true),
		'nickname' => array(T::String, true),
		'duckname' => array(T::String, true),
		// fysiek
		'geslacht' => array(T::Enumeration, false, Geslacht::class),
		'gebdatum' => array(T::Date),
		'sterfdatum' => array(T::Date, true),
		// getrouwd
		'echtgenoot' => array(T::UID, true),
		'adresseringechtpaar' => array(T::String, true),
		'ontvangtcontactueel' => array(T::Enumeration, false, OntvangtContactueel::class),
		// adres
		'adres' => array(T::String),
		'postcode' => array(T::String),
		'woonplaats' => array(T::String),
		'land' => array(T::String),
		'mobiel' => array(T::String),
		'telefoon' => array(T::String, true),
		'o_adres' => array(T::String, true),
		'o_postcode' => array(T::String, true),
		'o_woonplaats' => array(T::String, true),
		'o_land' => array(T::String, true),
		'o_telefoon' => array(T::String, true),
		// contact
		'email' => array(T::String),
		'linkedin' => array(T::String, true),
		'website' => array(T::String, true),
		// studie
		'studie' => array(T::String, true),
		'studiejaar' => array(T::Integer, true),
		'beroep' => array(T::String, true),
		// lidmaatschap
		'lidjaar' => array(T::Integer),
		'lidafdatum' => array(T::Date, true),
		'status' => array(T::Enumeration, false, LidStatus::class),
		// geld
		'bankrekening' => array(T::String, true),
		'machtiging' => array(T::Boolean, true),
		// verticale
		'moot' => array(T::Char, true),
		'verticale' => array(T::Char, true),
		'verticaleleider' => array(T::Boolean, true),
		'kringcoach' => array(T::Boolean, true),
		// civi-gegevens
		'patroon' => array(T::UID, true),
		'corvee_punten' => array(T::Integer, true),
		'corvee_punten_bonus' => array(T::Integer, true),
		// Persoonlijk
		'eetwens' => array(T::String, true),
		'lengte' => array(T::Integer),
		'kerk' => array(T::String, true),
		'muziek' => array(T::String, true),
		'zingen' => array(T::String, true),
		'vrienden' => array(T::Text, true),
		'middelbareSchool' => array(T::String, true),
		// novitiaat
		'novitiaat' => array(T::Text, true),
		'novitiaatBijz' => array(T::Text, true),
		'medisch' => array(T::Text, true),
		'startkamp' => array(T::String, true),
		'matrixPlek' => array(T::String, true),
		'novietSoort' => array(T::String, true),
		'kgb' => array(T::Text, true)
	);

	protected static $computed_attributes = [
		'primary_email' => [T::String],
		'account' => [Account::class],
		'kring' => [Kring::class],
	];
	/**
	 * In $properties_lidstatus kan per property worden aangegeven voor welke lidstatusen deze nodig. Bij wijziging van
	 * lidstatus wordt een property verwijderd als deze niet langer nodig is.
	 */
	public static $properties_lidstatus = [
		'o_adres' => [LidStatus::Lid, LidStatus::Gastlid, LidStatus::Noviet],
		'o_postcode' => [LidStatus::Lid, LidStatus::Gastlid, LidStatus::Noviet],
		'o_woonplaats' => [LidStatus::Lid, LidStatus::Gastlid, LidStatus::Noviet],
		'o_land' => [LidStatus::Lid, LidStatus::Gastlid, LidStatus::Noviet],
		'o_telefoon' => [LidStatus::Lid, LidStatus::Gastlid, LidStatus::Noviet],
		'eetwens' => [LidStatus::Lid, LidStatus::Gastlid, LidStatus::Noviet, LidStatus::Kringel],
		'vrienden' => [LidStatus::Noviet],
		// novitiaat
		'novitiaat' => [LidStatus::Noviet],
		'novitiaatBijz' => [LidStatus::Noviet],
		'medisch' => [LidStatus::Noviet],
		'startkamp' => [LidStatus::Noviet],
		'matrixPlek' => [LidStatus::Noviet],
		'novietSoort' => [LidStatus::Noviet],
		'kgb' => [LidStatus::Noviet]
	];
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'profielen';

	public function getUUID() {
		return $this->uid . '@csrdelft.nl';
	}

	public function magBewerken() {
		if (LoginModel::mag(P_LEDEN_MOD)) {
			return true;
		}
		if ($this->uid == 'x999') {
			return false;
		}
		if ($this->uid === LoginModel::getUid()) {
			return true;
		}
		if ($this->status === LidStatus::Noviet AND LoginModel::mag('commissie:NovCie')) {
			return true;
		}
		return false;
	}

	public function getAccount() {
		return AccountModel::get($this->uid);
	}

	public function getPrimaryEmail() {
		if (AccountModel::existsUid($this->uid)) {
			return $this->getAccount()->email;
		}
		return $this->email;
	}

	/**
	 * Geef een array met contactgegevens terug, als de velden niet leeg zijn.
	 *
	 * TODO: aparte tabellen voor multiple email, telefoon, etc...
	 */
	public function getContactgegevens() {
		return array_filter_empty(array(
			'Email' => $this->getPrimaryEmail(),
			'LinkedIn' => $this->linkedin,
			'Website' => $this->website
		));
	}

	public function getAdres() {
		return $this->adres . ' ' . $this->postcode . ' ' . $this->woonplaats;
	}

	public function getFormattedAddress() {
		return $this->adres . "\n" .
			$this->postcode . " " . $this->woonplaats . "\n" .
			$this->land;
	}

	public function getFormattedAddressOuders() {
		return $this->o_adres . "\n" .
			$this->o_postcode . " " . $this->o_woonplaats . "\n" .
			$this->o_land;
	}

	public function isJarig() {
		return substr($this->gebdatum, 5, 5) === date('m-d');
	}

	public function getJarigOver() {
		$verjaardag = strtotime(date('Y') . '-' . date('m-d', strtotime($this->gebdatum)));
		$nu = strtotime(date('Y-m-d'));
		if ($verjaardag < $nu) {
			$verjaardag = strtotime('+1 year', $verjaardag);
		}
		$dagen = round(($verjaardag - $nu) / 86400);
		if ($dagen == 0) {
			return true;
		} else {
			return $dagen;
		}
	}

	/**
	 * implements Agendeerbaar
	 *
	 * We maken een lid Agendeerbaar, zodat het in de agenda kan. Het is
	 * een beetje vieze hack omdat Agendeerbaar een enkele activiteit
	 * verwacht, terwijl een verjaardag een periodieke activiteit (elk
	 * jaar) is.
	 *
	 * @return int timestamp
	 */
	public function getBeginMoment() {
		$jaar = date('Y');
		if (isset($GLOBALS['agenda_jaar'], $GLOBALS['agenda_maand'])) { //FIEES, Patrick.
			/*
			 * Punt is dat we het goede (opgevraagde) jaar erbij moeten zetten,
			 * anders gaat het mis op randen van weken en jaren.
			 * De maand is ook nodig, anders gaat het weer mis met de weken in januari, want dan schuift
			 * alles doordat het jaar nog op het restje van de vorige maand staat.
			 */
			$jaar = $GLOBALS['agenda_jaar'];
			if ($GLOBALS['agenda_maand'] == 1 AND substr($this->gebdatum, 5, 2) == $GLOBALS['agenda_maand']) {
				$jaar += 1;
			}
		}
		$datum = $jaar . '-' . substr($this->gebdatum, 5, 5) . ' 00:00:00'; // 1 b'vo
		return strtotime($datum);
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 3600;
	}

	public function isHeledag() {
		return true;
	}

	public function getTitel() {
		return $this->getNaam('civitas');
	}

	public function getBeschrijving() {
		$jaar = isset($GLOBALS['agenda_jaar']) ? $GLOBALS['agenda_jaar'] : date('Y');
		return $this->getTitel() . ' wordt ' . ($jaar - date('Y', strtotime($this->gebdatum))) . ' jaar';
	}

	public function getLocatie() {
		return $this->getAdres();
	}

	public function getUrl() {
		return '/profiel/' . $this->uid;
	}

	public function getLink($vorm = 'civitas') {
		if (!LoginModel::mag(P_LEDEN_READ) OR in_array($this->uid, array('x999', 'x101', 'x027', 'x222', '4444'))) {
			if ($vorm === 'pasfoto' AND LoginModel::mag(P_LEDEN_READ)) {
				return $this->getPasfotoTag();
			}
			return $this->getNaam();
		}
		$naam = $this->getNaam($vorm);
		if ($vorm === 'pasfoto') {
			$naam = $this->getPasfotoTag();
		} elseif ($this->lidjaar === 2013) {
			$naam = CsrBB::parse('[neuzen]' . $naam . '[/neuzen]');
		}
		$k = '';
		if ($vorm !== 'pasfoto' AND LidInstellingenModel::get('layout', 'visitekaartjes') == 'ja') {
			$title = '';
		} else {
			$title = ' title="' . htmlspecialchars($this->getNaam('volledig')) . '"';
		}
		$l = '<a href="/profiel/' . $this->uid . '"' . $title . ' class="lidLink ' . htmlspecialchars($this->status) . '">';
		if ($vorm !== 'pasfoto' AND ($vorm === 'leeg' OR LidInstellingenModel::get('layout', 'visitekaartjes') == 'ja')) {
			$k = '<span';
			if ($vorm !== 'leeg') {
				$k .= ' class="hoverIntent"';
			}
			$k .= '><div style="margin-top: -15px; margin-left: -15px;" class="';
			if ($vorm !== 'leeg') {
				$k .= 'hoverIntentContent ';
			}
			$k .= 'visitekaartje';
			if ($this->isJarig()) {
				$k .= ' jarig';
			}
			if ($vorm === 'leeg') {
				$k .= '" style="display: block; position: static;';
			} else {
				$k .= ' init';
			}
			$k .= '">';
			$k .= $this->getPasfotoTag(false);
			$k .= '<div class="uid uitgebreid"><a href="/gesprekken/?zoek=' . urlencode($this->getNaam('civitas')) . '" class="lichtgrijs" title="Gesprek"><span class="glyphicon glyphicon-comment" aria-hidden="true"></span></a></div>';
			if (AccountModel::existsUid($this->uid) AND LoginModel::instance()->maySuTo($this->getAccount())) {
				$k .= '<div class="uid uitgebreid">';
				$k .= '<a href="/su/' . $this->uid . '" title="Su naar dit lid">' . $this->uid . '</a>';
				$k .= '</div>';
			}
			$k .= '<p class="naam">' . $l . $this->getNaam('volledig') . '&nbsp;' . LidStatus::getChar($this->status);
			$k .= '</a></p>';
			$k .= '<p>' . $this->lidjaar;
			$verticale = $this->getVerticale();
			if ($verticale) {
				$k .= ' ' . $verticale->naam;
			}
			$k .= '</p>';
			$bestuurslid = BestuursLedenModel::instance()->find('uid = ?', array($this->uid), null, null, 1)->fetch();
			if ($bestuurslid) {
				$bestuur = BesturenModel::get($bestuurslid->groep_id);
				$k .= '<p><a href="' . $bestuur->getUrl() . '">' . GroepStatus::getChar($bestuur->status) . ' ' . $bestuurslid->opmerking . '</a></p>';
			}
			foreach (CommissieLedenModel::instance()->find('uid = ?', array($this->uid), null, 'lid_sinds DESC') as $commissielid) {
				$commissie = CommissiesModel::get($commissielid->groep_id);
				if ($commissie->status === GroepStatus::HT) {
					$k .= '<p>';
					if (!empty($commissielid->opmerking)) {
						$k .= $commissielid->opmerking . '<br />';
					}
					$k .= '<a href="' . $commissie->getUrl() . '">' . $commissie->naam . '</a></p>';
				}
			}
			$k .= '</div>';
			if ($vorm === 'leeg') {
				$naam = $k . $naam;
			} else {
				$naam = $k . $l . $naam . '</a>';
			}
			return '<div class="inline">' . $naam . '</span></div>';
		}
		return $l . $naam . '</a>';
	}

	//einde implements Agendeerbaar

	/**
	 * Naam met verschillende weergave-mogelijkheden.
	 *
	 * @param string $vorm Zie switch()
	 * @param bool $force Forceer een type ongeacht of de gebruiker ingelogd is
	 * @return string
	 */
	public function getNaam($vorm = 'volledig', $force = false) {
		if ($vorm === 'user') {
			$vorm = LidInstellingenModel::get('forum', 'naamWeergave');
		}
		if (!$force AND !LoginModel::mag(P_LOGGED_IN)) {
			$vorm = 'civitas';
		}
		switch ($vorm) {

			case 'leeg':
				$naam = '';
				break;

			case 'volledig':
				if (empty($this->voornaam)) {
					$naam = $this->voorletters . ' ';
				} else {
					$naam = $this->voornaam . ' ';
				}
				if (!empty($this->tussenvoegsel)) {
					$naam .= $this->tussenvoegsel . ' ';
				}
				$naam .= $this->achternaam;
				break;

			case 'streeplijst':
				$naam = $this->achternaam . ', ';
				if (!empty($this->tussenvoegsel)) {
					$naam .= $this->tussenvoegsel . ', ';
				}
				$naam .= $this->voornaam;
				break;

			case 'voorletters':
				$naam = $this->voorletters . ' ';
				if (!empty($this->tussenvoegsel)) {
					$naam .= $this->tussenvoegsel . ' ';
				}
				$naam .= $this->achternaam;
				break;

			case 'bijnaam':
				if (!empty($this->nickname)) {
					$naam = $this->nickname;
					break;
				}
			// fall through

			case 'Duckstad':
				if (!empty($this->duckname)) {
					$naam = $this->duckname;
					break;
				}
			// fall through

			case 'civitas':
				// noviet
				if ($this->status === LidStatus::Noviet) {
					$naam = 'Noviet ' . $this->voornaam;
					if (!empty($this->postfix)) {
						$naam .= ' ' . $this->postfix;
					}
				} elseif ($this->isLid() OR $this->isOudlid()) {
					// voor novieten is het Dhr./ Mevr.
					if (LoginModel::getProfiel()->status === LidStatus::Noviet) {
						$naam = ($this->geslacht === Geslacht::Vrouw) ? 'Mevr. ' : 'Dhr. ';
					} else {
						$naam = ($this->geslacht === Geslacht::Vrouw) ? 'Ama. ' : 'Am. ';
					}
					if (!empty($this->tussenvoegsel)) {
						$naam .= ucfirst($this->tussenvoegsel) . ' ';
					}
					$naam .= $this->achternaam;
					if (!empty($this->postfix)) {
						$naam .= ' ' . $this->postfix;
					}
					// status char weergeven bij oudleden en ereleden
					if ($this->isOudlid()) {
						$naam .= ' ' . LidStatus::getChar($this->status);
					}
				} // geen lid
				else {
					if (LoginModel::mag(P_LEDEN_READ)) {
						$naam = $this->voornaam . ' ';
					} else {
						$naam = $this->voorletters . ' ';
					}
					if (!empty($this->tussenvoegsel)) {
						$naam .= $this->tussenvoegsel . ' ';
					}
					$naam .= $this->achternaam;
					// status char weergeven bij kringels
					if ($this->status === LidStatus::Kringel) {
						$naam .= ' ' . LidStatus::getChar($this->status);
					}
				}

				break;

			case 'aaidrom': // voor een 1 aprilgrap ooit
				$naam = aaidrom($this->voornaam, $this->tussenvoegsel, $this->achternaam);
				break;

			default:
				$naam = 'Onbekend formaat $vorm: ' . htmlspecialchars($vorm);
		}
		return $naam;
	}

	/**
	 * Kijkt of er een pasfoto voor het gegeven uid is, en geef die terug.
	 * Geef anders een standaard-plaatje terug.
	 *
	 * @param boolean $square Geef een pad naar een vierkante (150x150px) versie terug. (voor google contacts sync)
	 * @return string
	 */
	public function getPasfotoPath($vierkant = false, $vorm = 'user') {
		$path = null;
		if (LoginModel::mag(P_OUDLEDEN_READ)) {
			// in welke (sub)map moeten we zoeken?
			if ($vierkant) {
				$folders = array('');
			} else {
				if ($vorm === 'user') {
					$vorm = LidInstellingenModel::get('forum', 'naamWeergave');
				}
				$folders = array($vorm . '/', '');
			}
			// loop de volgende folders af op zoek naar de gevraagde pasfoto vorm
			foreach ($folders as $subfolder) {
				foreach (array('png', 'jpeg', 'jpg', 'gif') as $validExtension) {
					if (file_exists(PASFOTO_PATH . $subfolder . $this->uid . '.' . $validExtension)) {
						$path = $subfolder . $this->uid . '.' . $validExtension;
						break;
					}
				}
				if ($path) {
					break;
				} elseif ($vorm === 'Duckstad') {
					$path = $vorm . '/eend.jpg';
					break;
				}
			}
		}
		if (!$path) {
			$path = 'geen-foto.jpg';
		}
		// als het vierkant moet, kijken of de vierkante bestaat, en anders maken
		if ($vierkant) {
			$crop = '' . $this->uid . '.vierkant.png';
			if (!file_exists(PASFOTO_PATH . $crop)) {
				square_crop(PASFOTO_PATH . $path, PASFOTO_PATH . $crop, 150);
			}
			return $crop;
		}
		return $path;
	}

	public function getPasfotoTag($cssClass = 'pasfoto', $vierkant = false) {
		return '<img class="' . htmlspecialchars($cssClass) . '" src="/plaetjes/pasfoto/' . $this->getPasfotoPath($vierkant) . '" alt="Pasfoto van ' . $this->getNaam('volledig') . '" />';
	}

	public function getKinderen() {
		if (!isset($this->kinderen)) {
			$this->kinderen = ProfielModel::instance()->find('patroon = ?', array($this->uid));
		}
		return $this->kinderen;
	}

	public function hasKinderen() {
		$this->getKinderen();
		return !empty($this->kinderen);
	}

	public function isLid() {
		return LidStatus::isLidLike($this->status);
	}

	public function isOudlid() {
		return LidStatus::isOudlidLike($this->status);
	}

	public function getWoonoord() {
		$woonoorden = WoonoordenModel::instance()->getGroepenVoorLid($this->uid, GroepStatus::HT);
		if (empty($woonoorden)) {
			return false;
		}
		return reset($woonoorden);
	}

	public function getVerticale() {
		return VerticalenModel::get($this->verticale);
	}

	public function getKring() {
		$kringen = KringenModel::instance()->getGroepenVoorLid($this->uid, GroepStatus::HT);
		if (empty($kringen)) {
			return false;
		}
		return reset($kringen);
	}

	/**
	 * Vraag CiviSaldo aan CiviSaldosysteem (staat gewoon in CiviSaldo-tabel).
	 *
	 * @return float
	 */
	public function getCiviSaldo() {
		$saldo = CiviSaldoModel::instance()->getSaldo($this->uid);
		if ($saldo) {
			return $saldo->saldo / (float) 100;
		}

		return 0;
	}

	/**
	 * Controleer of een lid al in de google-contacts-lijst staat.
	 *
	 * @return boolean
	 */
	public function isInGoogleContacts() {
		try {
			if (!GoogleSync::isAuthenticated()) {
				return false;
			}
			return !is_null(GoogleSync::instance()->existsInGoogleContacts($this));
		} catch (CsrGebruikerException $e) {
			setMelding($e->getMessage(), 0);
			return false;
		} catch (RequestException $e) {
			setMelding($e->getMessage(), -1);
			return false;
		}
	}

	public function propertyMogelijk(string $name) {
		if (!array_key_exists($name, Profiel::$properties_lidstatus)) {
			return true;
		}
		return in_array($this->status, Profiel::$properties_lidstatus[$name]);
	}
}
