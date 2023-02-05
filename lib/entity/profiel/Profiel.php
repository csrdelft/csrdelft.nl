<?php

namespace CsrDelft\entity\profiel;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\common\Util\TextUtil;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\LidToestemming;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\security\Account;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielLogGroup;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\groepen\KringenRepository;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\datatable\DataTableColumn;
use CsrDelft\view\formulier\DisplayEntity;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Proxy;

/**
 * Profiel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Profiel van een lid. Agendeerbaar vanwege verjaardag in agenda.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\ProfielRepository")
 * @ORM\Table("profielen", indexes={
 *   @ORM\Index(name="voornaam", columns={"voornaam"}),
 *   @ORM\Index(name="achternaam", columns={"achternaam"}),
 *   @ORM\Index(name="verticale", columns={"verticale"}),
 *   @ORM\Index(name="nickname", columns={"nickname"}),
 *   @ORM\Index(name="status", columns={"status"})
 * })
 */
class Profiel implements Agendeerbaar, DisplayEntity
{
	public function __construct()
	{
		$this->kinderen = new ArrayCollection();
	}

	/**
	 * @ORM\Id()
	 * @ORM\Column(type="uid")
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
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
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
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $tussenvoegsel;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $achternaam;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $postfix;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $nickname;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $duckname;
	// fysiek
	/**
	 * @ORM\Column(type="enumGeslacht")
	 * @var Geslacht
	 */
	public $geslacht;
	/**
	 * @ORM\Column(type="date")
	 * @var DateTimeImmutable
	 */
	public $gebdatum;
	/**
	 * @ORM\Column(type="date", nullable=true)
	 * @var DateTimeImmutable|null
	 */
	public $sterfdatum;
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	public $lengte;
	// getrouwd
	/**
	 * @ORM\Column(type="uid", nullable=true)
	 * @var string|null
	 */
	public $echtgenoot;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $adresseringechtpaar;
	/**
	 * @ORM\Column(type="enumOntvangtContactueel")
	 * @var OntvangtContactueel
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
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $telefoon;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $o_adres;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $o_postcode;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $o_woonplaats;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $o_land;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $o_telefoon;
	// contact
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $email;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $sec_email;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $mobiel;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $linkedin;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $website;
	// studie
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $studie;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $studiejaar;
	/**
	 * @ORM\Column(type="string", nullable=true)
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
	 * @ORM\Column(type="date", nullable=true)
	 * @var DateTimeImmutable|null
	 */
	public $lidafdatum;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $status;
	// geld
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $bankrekening;
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @var boolean|null
	 */
	public $machtiging;
	/**
	 * @ORM\Column(type="boolean", nullable=true, name="toestemmingAfschrijven")
	 * @var boolean|null
	 */
	public $toestemmingAfschrijven;
	// verticale
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $moot;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $verticale;
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @var boolean|null
	 */
	public $verticaleleider;
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @var boolean|null
	 */
	public $kringcoach;
	// civi-gegevens
	/**
	 * @ORM\Column(type="uid", nullable=true)
	 * @var string
	 */
	public $patroon;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $eetwens;
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var integer|null
	 */
	public $corvee_punten;
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var integer|null
	 */
	public $corvee_punten_bonus;
	// novitiaat
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	public $novitiaat;
	/**
	 * @ORM\Column(type="text", nullable=true, name="novitiaatBijz")
	 * @var string|null
	 */
	public $novitiaatBijz;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	public $medisch;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $startkamp;
	/**
	 * @ORM\Column(type="string", nullable=true, name="matrixPlek")
	 * @var string|null
	 */
	public $matrixPlek;
	/**
	 * @ORM\Column(type="string", nullable=true, name="novietSoort")
	 * @var string|null
	 */
	public $novietSoort;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	public $kgb;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	public $vrienden;
	/**
	 * @ORM\Column(type="string", nullable=true, name="middelbareSchool")
	 * @var string
	 */
	public $middelbareSchool;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $huisarts;
	/**
	 * @ORM\Column(type="string", nullable=true, name="huisartsPlaats")
	 * @var string
	 */
	public $huisartsPlaats;
	/**
	 * @ORM\Column(type="string", nullable=true, name="huisartsTelefoon")
	 * @var string|null
	 */
	public $huisartsTelefoon;
	// overig
	/**
	 * @ORM\Column(type="string", nullable=true, name="profielOpties")
	 * @var string
	 */
	public $profielOpties;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $kerk;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $muziek;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	public $zingen;

	/**
	 * @var Account|null
	 * @ORM\OneToOne(targetEntity="CsrDelft\entity\security\Account", mappedBy="profiel")
	 */
	public $account;

	/**
	 * @var LidToestemming[]
	 * @ORM\OneToMany(targetEntity="CsrDelft\entity\LidToestemming", mappedBy="profiel")
	 */
	public $toestemmingen;

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
		'eetwens' => [
			LidStatus::Lid,
			LidStatus::Gastlid,
			LidStatus::Noviet,
			LidStatus::Kringel,
		],
		'vrienden' => [LidStatus::Noviet],
		// novitiaat
		'novitiaat' => [LidStatus::Noviet],
		'novitiaatBijz' => [LidStatus::Noviet],
		'medisch' => [LidStatus::Noviet],
		'startkamp' => [LidStatus::Noviet],
		'matrixPlek' => [LidStatus::Noviet],
		'novietSoort' => [LidStatus::Noviet],
		'kgb' => [LidStatus::Noviet],
		'huisarts' => [LidStatus::Noviet],
		'huisartsPlaats' => [LidStatus::Noviet],
		'huisartsTelefoon' => [LidStatus::Noviet],
	];

	public function getUUID()
	{
		return $this->uid . '@csrdelft.nl';
	}

	public function magBewerken()
	{
		if (LoginService::mag(P_LEDEN_MOD)) {
			return true;
		}
		if ($this->uid == LoginService::UID_EXTERN) {
			return false;
		}
		if ($this->uid === LoginService::getUid()) {
			return true;
		}
		if (
			$this->status === LidStatus::Noviet &&
			LoginService::mag('commissie:NovCie')
		) {
			return true;
		}
		return false;
	}

	public function getAccount()
	{
		return $this->account;
	}

	public function getPrimaryEmail()
	{
		if ($this->account != null) {
			return $this->account->email;
		}
		return $this->email;
	}

	/**
	 * @return array
	 */
	public function getEmailOntvanger()
	{
		return [$this->getPrimaryEmail() => $this->getNaam()];
	}

	/**
	 * Geef een array met contactgegevens terug, als de velden niet leeg zijn.
	 *
	 * TODO: aparte tabellen voor multiple email, telefoon, etc...
	 */
	public function getContactgegevens()
	{
		return ArrayUtil::array_filter_empty([
			'Email' => $this->getPrimaryEmail(),
			'LinkedIn' => $this->linkedin,
			'Website' => $this->website,
		]);
	}

	public function getAdres()
	{
		return $this->adres . ' ' . $this->postcode . ' ' . $this->woonplaats;
	}

	public function getFormattedAddress()
	{
		return $this->adres .
			"\n" .
			$this->postcode .
			' ' .
			$this->woonplaats .
			"\n" .
			$this->land;
	}

	public function getFormattedAddressOuders()
	{
		return $this->o_adres .
			"\n" .
			$this->o_postcode .
			' ' .
			$this->o_woonplaats .
			"\n" .
			$this->o_land;
	}

	public function isJarig()
	{
		return $this->gebdatum != null &&
			substr(DateUtil::dateFormatIntl($this->gebdatum, DATE_FORMAT), 5, 5) ===
				date('m-d');
	}

	/**
	 * Vervormt kommagescheiden opties naar lijst,
	 * voegt lichting toe en voegt verjaardag toe indien van toepassing.
	 */
	public function getProfielOpties()
	{
		$opties = $this->profielOpties
			? array_map(function ($a) {
				return trim($a);
			}, explode(',', $this->profielOpties))
			: [];
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
	public function getProfielClasses()
	{
		return implode(' ', $this->getProfielOpties());
	}

	public function getJarigOver()
	{
		$verjaardag = strtotime(
			date('Y') . '-' . date('m-d', $this->gebdatum->getTimestamp())
		);
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
	public function getBeginMoment(): DateTimeImmutable
	{
		$dag = $this->gebdatum->format('m-d');
		if (isset($GLOBALS['agenda_van'], $GLOBALS['agenda_tot'])) {
			//FIEES, Patrick.
			/*
			 * Punt is dat we het goede (opgevraagde) jaar erbij moeten zetten,
			 * anders gaat het mis op randen van weken en jaren.
			 * De maand is ook nodig, anders gaat het weer mis met de weken in januari, want dan schuift
			 * alles doordat het jaar nog op het restje van de vorige maand staat.
			 */
			$van = $GLOBALS['agenda_van'];
			$tot = $GLOBALS['agenda_tot'];

			$jaar = $van->format('Y');
			do {
				$datum = date_create_immutable($jaar . '-' . $dag . ' 00:00:00');
				$jaar++;
			} while ($datum < $van);
		} elseif (isset($GLOBALS['agenda_jaar'])) {
			$datum = date_create_immutable(
				$GLOBALS['agenda_jaar'] . '-' . $dag . ' 00:00:00'
			);
		} else {
			$datum = date_create_immutable(date('Y') . '-' . $dag . ' 00:00:00');
		}
		return $datum;
	}

	public function getEindMoment(): DateTimeImmutable
	{
		return $this->getBeginMoment()->add(new \DateInterval('PT1H'));
	}

	public function isHeledag()
	{
		return true;
	}

	public function getTitel()
	{
		return $this->getNaam('civitas');
	}

	public function getBeschrijving()
	{
		$leeftijd =
			$this->getBeginMoment()->format('Y') - $this->gebdatum->format('Y');

		if ($leeftijd == 0) {
			return $this->getTitel() . ' wordt geboren';
		}

		if ($leeftijd < 0) {
			return $this->getTitel() .
				' wordt over ' .
				$leeftijd * -1 .
				' jaar geboren.';
		}

		return $this->getTitel() . ' wordt ' . $leeftijd . ' jaar';
	}

	public function getLocatie()
	{
		return $this->getAdres();
	}

	public function getUrl()
	{
		return '/profiel/' . $this->uid;
	}

	public function getLink($vorm = 'civitas')
	{
		if (
			!LoginService::mag(P_LEDEN_READ) ||
			in_array($this->uid, [
				LoginService::UID_EXTERN,
				'x101',
				'x027',
				'x222',
				'4444',
			])
		) {
			if ($vorm === 'pasfoto' && LoginService::mag(P_LEDEN_READ)) {
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
		if (
			$vorm !== 'pasfoto' &&
			lid_instelling('layout', 'visitekaartjes') == 'ja'
		) {
			$title = '';
		} else {
			$title = ' title="' . htmlspecialchars($this->getNaam('volledig')) . '"';
		}
		$l =
			'<a href="/profiel/' .
			$this->uid .
			'"' .
			$title .
			' class="lidLink ' .
			htmlspecialchars($this->status) .
			'" data-lid="' .
			$this->uid .
			'" data-lid-naam="' .
			$this->getNaam($vorm) .
			'">';
		if (
			$vorm !== 'pasfoto' &&
			lid_instelling('layout', 'visitekaartjes') == 'ja'
		) {
			return '<span data-visite="' .
				$this->uid .
				'" data-lid="' .
				$this->uid .
				'" data-lid-naam="' .
				$this->getNaam($vorm) .
				'"><a href="/profiel/' .
				$this->uid .
				'" class="lidLink ' .
				htmlspecialchars($this->status) .
				'">' .
				$naam .
				'</a></span>';
		} elseif ($vorm === 'leeg') {
			$twig = ContainerFacade::getContainer()->get('twig');

			return $twig->render('profiel/kaartje.html.twig', ['profiel' => $this]);
		}

		return $l . $naam . '</a>';
	}

	public function isTransparant()
	{
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
	public function getNaam($vorm = 'volledig', $force = false)
	{
		if ($vorm === 'user') {
			$vorm = lid_instelling('forum', 'naamWeergave');
		}
		if ($vorm != 'civitas' && !$force && !LoginService::mag(P_LOGGED_IN)) {
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
				} elseif ($this->isLid() || $this->isOudlid()) {
					// voor novieten is het Dhr./ Mevr.
					if (LoginService::getProfiel()->status === LidStatus::Noviet) {
						$naam = Geslacht::isVrouw($this->geslacht) ? 'Mevr. ' : 'Dhr. ';
					} else {
						$naam = Geslacht::isVrouw($this->geslacht) ? 'Ama. ' : 'Am. ';
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
						$naam .= ' ' . LidStatus::from($this->status)->getChar();
					}
				}
				// geen lid
				else {
					if (LoginService::mag(P_LEDEN_READ)) {
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
						$naam .= ' ' . LidStatus::from($this->status)->getChar();
					}
				}

				break;

			case 'aaidrom': // voor een 1 aprilgrap ooit
				$naam = TextUtil::aaidrom(
					$this->voornaam,
					$this->tussenvoegsel,
					$this->achternaam
				);
				break;

			case 'slug':
				$naam =
					str_replace(' ', '-', $this->getNaam('volledig')) . '-' . $this->uid;
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
	public function getPasfotoPath($vorm = 'user')
	{
		if ($vorm === 'user') {
			$vorm = lid_instelling('forum', 'naamWeergave');
		}

		if (!is_zichtbaar($this, 'profielfoto', 'intern')) {
			return '/images/geen-foto.jpg';
		}
		$path = $this->getPasfotoInternalPath($vorm);
		if ($path === null) {
			return '/images/geen-foto.jpg';
		}

		if (in_array($vorm, ['Duckstad', 'vierkant'])) {
			return "/profiel/pasfoto/$this->uid.$vorm.jpg";
		}

		return "/profiel/pasfoto/$this->uid.jpg";
	}

	public function getPasfotoInternalPath($vorm = 'user')
	{
		$path = null;
		if (LoginService::mag(P_OUDLEDEN_READ)) {
			// in welke (sub)map moeten we zoeken?
			if ($vorm == 'vierkant') {
				$folders = [''];
			} else {
				$folders = [$vorm . '/', ''];
			}
			// loop de volgende folders af op zoek naar de gevraagde pasfoto vorm
			foreach ($folders as $subfolder) {
				foreach (['png', 'jpeg', 'jpg', 'gif'] as $validExtension) {
					if (
						file_exists(
							PASFOTO_PATH . $subfolder . $this->uid . '.' . $validExtension
						)
					) {
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
				FileUtil::square_crop(PASFOTO_PATH . $path, PASFOTO_PATH . $crop, 150);
			}
			$path = $crop;
		}
		return PathUtil::safe_combine_path(PASFOTO_PATH, $path);
	}

	public function getPasfotoTag($cssClass = '')
	{
		return '<img class="pasfoto ' .
			htmlspecialchars($cssClass) .
			'" src="' .
			$this->getPasfotoPath() .
			'" alt="Pasfoto van ' .
			$this->getNaam('volledig') .
			'" />';
	}

	public function getPasfotoRounded()
	{
		return $this->getPasfotoTag('rounded-circle flex-shrink-0');
	}

	public function getPasfotoLink()
	{
		return $this->getPasfotoPath();
	}

	/**
	 * @var Profiel|null
	 * @ORM\ManyToOne(targetEntity="Profiel", inversedBy="kinderen")
	 * @ORM\JoinColumn(name="patroon", referencedColumnName="uid", nullable=true)
	 */
	private $patroonProfiel;

	public function getPatroonProfiel()
	{
		try {
			$patroonProfiel = $this->patroonProfiel;
			if ($patroonProfiel instanceof Proxy) {
				$patroonProfiel->__load();
			}
			return $patroonProfiel;
		} catch (EntityNotFoundException $ex) {
			return null;
		}
	}

	/**
	 * @var Profiel[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Profiel", mappedBy="patroonProfiel")
	 */
	public $kinderen;

	public function hasKinderen()
	{
		return $this->kinderen->count() !== 0;
	}

	public function getNageslachtGrootte()
	{
		$nageslacht = 0;
		foreach ($this->kinderen as $kind) {
			$nageslacht++;
			$nageslacht += $kind->getNageslachtGrootte();
		}

		return $nageslacht;
	}

	public function isLid()
	{
		return LidStatus::isLidLike($this->status);
	}

	public function isOudlid()
	{
		return LidStatus::isOudlidLike($this->status);
	}

	/**
	 * @return Woonoord|null
	 */
	public function getWoonoord()
	{
		/** @var Woonoord[] $woonoorden */
		$woonoorden = ContainerFacade::getContainer()
			->get(WoonoordenRepository::class)
			->getGroepenVoorLid($this, GroepStatus::HT);
		if (empty($woonoorden)) {
			return null;
		}
		return reset($woonoorden);
	}

	/**
	 * @return Verticale|null
	 */
	public function getVerticale()
	{
		return ContainerFacade::getContainer()
			->get(VerticalenRepository::class)
			->get($this->verticale);
	}

	/**
	 * @return Kring|null
	 */
	public function getKring()
	{
		$kringen = ContainerFacade::getContainer()
			->get(KringenRepository::class)
			->getGroepenVoorLid($this, GroepStatus::HT);
		if (empty($kringen)) {
			return null;
		}
		return reset($kringen);
	}

	/**
	 * Vraag CiviSaldo aan CiviSaldosysteem (staat gewoon in CiviSaldo-tabel).
	 *
	 * @return float
	 */
	public function getCiviSaldo()
	{
		$saldo = ContainerFacade::getContainer()
			->get(CiviSaldoRepository::class)
			->getSaldo($this->uid);
		if ($saldo) {
			return $saldo->saldo / (float) 100;
		}

		return 0;
	}

	public function propertyMogelijk(string $name)
	{
		if (!array_key_exists($name, Profiel::$properties_lidstatus)) {
			return true;
		}
		return in_array($this->status, Profiel::$properties_lidstatus[$name]);
	}

	public function getDataTableColumn()
	{
		return new DataTableColumn(
			$this->getLink('volledig'),
			$this->achternaam,
			$this->getNaam('volledig')
		);
	}

	public function getId()
	{
		return $this->uid;
	}

	public function getWeergave(): string
	{
		return $this->achternaam ? $this->getNaam('volledig') : '';
	}

	public function getChar()
	{
		return LidStatus::from($this->status)->getChar();
	}

	public function getLidStatusDescription()
	{
		return LidStatus::from($this->status)->getDescription();
	}

	public function getLeeftijd()
	{
		return $this->gebdatum->diff(date_create_immutable())->y;
	}
}
