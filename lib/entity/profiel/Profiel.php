<?php

namespace CsrDelft\entity\profiel;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\GoogleSync;
use CsrDelft\entity\commissievoorkeuren\VoorkeurVoorkeur;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielLogGroup;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\groepen\KringenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\datatable\DataTableColumn;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use GuzzleHttp\Exception\RequestException;


/**
 * Profiel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Profiel van een lid. Agendeerbaar vanwege verjaardag in agenda.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\ProfielRepository")
 * @ORM\Table("profielen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Profiel implements Agendeerbaar {
	/**
	 * @ORM\Id()
	 * @ORM\Column(type="string", length=4)
	 * @var string
	 */
	public $uid;
	/**
	 * @ORM\Column(type="changelog")
	 * @var ProfielLogGroup[]
	 */
	public $changelog;
	// naam
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $voornamen;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $voorletters;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $voornaam;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $tussenvoegsel;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $achternaam;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $postfix;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $nickname;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $duckname;
	// fysiek
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $geslacht;
	/**
	 * @ORM\Column(type="date")
	 * @var DateTime
	 */
	public $gebdatum;
	/**
	 * @ORM\Column(type="date")
	 * @var DateTime
	 */
	public $sterfdatum;
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	public $lengte;
	// getrouwd
	/**
	 * @ORM\Column(type="string", length=4)
	 * @var string
	 */
	public $echtgenoot;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $adresseringechtpaar;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $ontvangtcontactueel;
	// adres
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $adres;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $postcode;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $woonplaats;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $land;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $telefoon;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $o_adres;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $o_postcode;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $o_woonplaats;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $o_land;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $o_telefoon;
	// contact
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $email;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $sec_email;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */

	public $mobiel;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $linkedin;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $website;
	// studie
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $studie;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $studiejaar;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $beroep;
	// lidmaatschap
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	public $lidjaar;
	/**
	 * @ORM\Column(type="date")
	 * @var DateTime
	 */
	public $lidafdatum;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $status;
	// geld
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $bankrekening;
	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	public $machtiging;
	// verticale
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $moot;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $verticale;
	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	public $verticaleleider;
	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	public $kringcoach;
	// civi-gegevens
	/**
	 * @ORM\Column(type="string", length=4)
	 * @var string
	 */
	public $patroon;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $eetwens;
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	public $corvee_punten;
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	public $corvee_punten_bonus;
	// novitiaat
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string
	 */
	public $novitiaat;
	/**
	 * @ORM\Column(type="text", nullable=true, name="novitiaatBijz")
	 * @var string
	 */
	public $novitiaatBijz;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string
	 */
	public $medisch;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $startkamp;
	/**
	 * @ORM\Column(type="string", nullable=true, name="matrixPlek")
	 * @var string
	 */
	public $matrixPlek;
	/**
	 * @ORM\Column(type="string", nullable=true, name="novietSoort")
	 * @var string
	 */
	public $novietSoort;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string
	 */
	public $kgb;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string
	 */
	public $vrienden;
	/**
	 * @ORM\Column(type="string", nullable=true, name="middelbareSchool")
	 * @var string
	 */
	public $middelbareSchool;
	/**
	 * @ORM\Column(type="string", nullable=true, name="profielOpties")
	 * @var string
	 */
	public $profielOpties;
	// overig
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $kerk;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $muziek;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $zingen;

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

	public function getUUID() {
		return $this->uid . '@csrdelft.nl';
	}

	public function magBewerken() {
		if (LoginModel::mag(P_LEDEN_MOD)) {
			return true;
		}
		if ($this->uid == LoginModel::UID_EXTERN) {
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
		return substr($this->gebdatum->format(DATE_FORMAT), 5, 5) === date('m-d');
	}

	/**
	 * Vervormt kommagescheiden opties naar lijst,
	 * voegt lichting toe en voegt verjaardag toe indien van toepassing.
	 */
	public function getProfielOpties() {
		$opties = $this->profielOpties ? array_map(function($a) { return trim($a); }, explode(',', $this->profielOpties)) : [];
		$opties[] = "lichting-{$this->lidjaar}";
		if ($this->isJarig()) {
			$opties[] = 'jarig';
		}

		return $opties;
	}

	/**
	 * Vervormt kommagescheiden opties naar spatiegescheiden opties
	 * en voegt verjaardag toe indien van toepassing.
	 */
	public function getProfielClasses() {
		return implode(' ', $this->getProfielOpties());
	}

	public function getJarigOver() {
		$verjaardag = strtotime(date('Y') . '-' . date('m-d', $this->gebdatum->getTimestamp()));
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
		$dag = $this->gebdatum->format('m-d');
		if (isset($GLOBALS['agenda_van'], $GLOBALS['agenda_tot'])) { //FIEES, Patrick.
			/*
			 * Punt is dat we het goede (opgevraagde) jaar erbij moeten zetten,
			 * anders gaat het mis op randen van weken en jaren.
			 * De maand is ook nodig, anders gaat het weer mis met de weken in januari, want dan schuift
			 * alles doordat het jaar nog op het restje van de vorige maand staat.
			 */
			$van = $GLOBALS['agenda_van'];
			$tot = $GLOBALS['agenda_tot'];

			$datum = date('Y', $van) . '-' . $dag . ' 00:00:00';

			if (strtotime($datum) < strtotime($van) || strtotime($datum) > strtotime($tot)) {
				$datum = date('Y', $tot) . '-' . $dag . ' 00:00:00';
			}
		} else if (isset($GLOBALS['agenda_jaar'])) {
			$datum = $GLOBALS['agenda_jaar'] . '-' . $dag . ' 00:00:00';
		} else {
			$datum = date('Y') . '-' . $dag . ' 00:00:00'; // 1 b'vo
		}
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
		$leeftijd = date('Y', $this->getBeginMoment()) - date('Y', $this->gebdatum->getTimestamp());

		if ($leeftijd == 0) {
			return $this->getTitel() . ' wordt geboren';
		}

		return $this->getTitel() . ' wordt ' . $leeftijd . ' jaar';
	}

	public function getLocatie() {
		return $this->getAdres();
	}

	public function getUrl() {
		return '/profiel/' . $this->uid;
	}

	public function getLink($vorm = 'civitas') {
		if (!LoginModel::mag(P_LEDEN_READ) OR in_array($this->uid, array(LoginModel::UID_EXTERN, 'x101', 'x027', 'x222', '4444'))) {
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
		if ($vorm !== 'pasfoto' AND lid_instelling('layout', 'visitekaartjes') == 'ja') {
			$title = '';
		} else {
			$title = ' title="' . htmlspecialchars($this->getNaam('volledig')) . '"';
		}
		$l = '<a href="/profiel/' . $this->uid . '"' . $title . ' class="lidLink ' . htmlspecialchars($this->status) . '">';
		if ($vorm !== 'pasfoto' AND lid_instelling('layout', 'visitekaartjes') == 'ja') {
			return '<span data-visite="'.$this->uid.'"><a href="/profiel/' . $this->uid . '" class="lidLink ' . htmlspecialchars($this->status) . '">' . $naam . '</a></span>';
		} else if ($vorm === 'leeg') {
			return view('profiel.kaartje', ['profiel' => $this])->getHtml();
		}

		return $l . $naam . '</a>';
	}

	public function isTransparant() {
		return true;
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
			$vorm = lid_instelling('forum', 'naamWeergave');
		}
		if ($vorm != 'civitas' AND !$force AND !LoginModel::mag(P_LOGGED_IN)) {
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
	 * @param string $vorm
	 * @return string
	 */
	public function getPasfotoPath($vorm = 'user') {
		if ($vorm === 'user') {
			$vorm = lid_instelling('forum', 'naamWeergave');
		}

		if (in_array($vorm, ['Duckstad', 'vierkant'])) {
			return "/profiel/pasfoto/$this->uid.$vorm.jpg";
		}

		return "/profiel/pasfoto/$this->uid.jpg";
	}

	public function getPasfotoInternalPath($vierkant = false, $vorm = 'user') {
		$path = null;
		if (LoginModel::mag(P_OUDLEDEN_READ)) {
			// in welke (sub)map moeten we zoeken?
			if ($vorm == 'vierkant') {
				$folders = [''];
			} else {
				$folders = [$vorm . '/', ''];
			}
			// loop de volgende folders af op zoek naar de gevraagde pasfoto vorm
			foreach ($folders as $subfolder) {
				foreach (['png', 'jpeg', 'jpg', 'gif'] as $validExtension) {
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
			return null;
		}
		// als het vierkant moet, kijken of de vierkante bestaat, en anders maken
		if ($vorm == 'vierkant') {
			$crop = '' . $this->uid . '.vierkant.png';
			if (!file_exists(PASFOTO_PATH . $crop)) {
				square_crop(PASFOTO_PATH . $path, PASFOTO_PATH . $crop, 150);
			}
			$path = $crop;
		}
		return safe_combine_path(PASFOTO_PATH, $path);
	}

	public function getPasfotoTag($cssClass = 'pasfoto') {
		return '<img class="' . htmlspecialchars($cssClass) . '" src="' . $this->getPasfotoPath() . '" alt="Pasfoto van ' . $this->getNaam('volledig') . '" />';
	}

	private $kinderen;

	/**
	 * @return Profiel[]
	 */
	public function getKinderen() {
		if ($this->kinderen == null) {
			$container = ContainerFacade::getContainer();
			$this->kinderen = $container->get(ProfielRepository::class)->ormFind('patroon = ?', array($this->uid));
		}

		return $this->kinderen;
	}

	public function hasKinderen() {
		return count($this->getKinderen()) !== 0;
	}

	public function getNageslachtGrootte() {
		$nageslacht = 0;
		foreach ($this->getKinderen() as $kind) {
			$nageslacht++;
			$nageslacht += $kind->getNageslachtGrootte();
		}

		return $nageslacht;
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
		return VerticalenModel::instance()->get($this->verticale);
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

	public function getDataTableColumn() {
		return new DataTableColumn($this->getLink('volledig'), $this->achternaam, $this->getNaam('volledig'));
	}
}
