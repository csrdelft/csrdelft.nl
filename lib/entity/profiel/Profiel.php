<?php

namespace CsrDelft\entity\profiel;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\common\Util\TextUtil;
use CsrDelft\entity\LidToestemming;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\groepen\Woonoord;
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Persistence\Proxy;
use const P_LEDEN_MOD;

/**
 * Profiel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Profiel van een lid. Agendeerbaar vanwege verjaardag in agenda.
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\ProfielRepository::class)]
#[ORM\Table('profielen')]
#[ORM\Index(name: 'voornaam', columns: ['voornaam'])]
#[ORM\Index(name: 'achternaam', columns: ['achternaam'])]
#[ORM\Index(name: 'verticale', columns: ['verticale'])]
#[ORM\Index(name: 'nickname', columns: ['nickname'])]
#[ORM\Index(name: 'status', columns: ['status'])]
class Profiel implements Agendeerbaar, DisplayEntity
{
	public function __construct(
		// FIXME(#1231): Hack voor ProfielEntityField initialisatie (zie issue #1231)
		#[Id, Column(type: 'uid')] public ?string $uid = null
	)
	{
		$this->toestemmingen = new ArrayCollection();
		$this->kinderen = new ArrayCollection();
	}
	/**
	 * @var ProfielLogGroup[]
	 */
	#[ORM\Column(type: 'changelog')]
	public $changelog;
	// naam
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $voornamen;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $voorletters;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $voornaam;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $tussenvoegsel;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $achternaam;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $postfix;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $nickname;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $duckname;

	#[ORM\Column(type: 'string', enumType: Geslacht::class)]
	public Geslacht $geslacht;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'date')]
	public $gebdatum;
	/**
	 * @var DateTimeImmutable|null
	 */
	#[ORM\Column(type: 'date', nullable: true)]
	public $sterfdatum;
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	public $lengte;
	// getrouwd
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'uid', nullable: true)]
	public $echtgenoot;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $adresseringechtpaar;
	/**
	 * @var OntvangtContactueel
	 */
	#[ORM\Column(type: 'enumOntvangtContactueel')]
	public $ontvangtcontactueel;
	// adres
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $adres;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $postcode;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $woonplaats;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $land;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $telefoon;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $o_adres;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $o_postcode;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $o_woonplaats;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $o_land;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $o_telefoon;
	// contact
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $email;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $sec_email;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $mobiel;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $linkedin;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $website;
	// studie
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $studie;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $studiejaar;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $beroep;
	// lidmaatschap
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	public $lidjaar;
	/**
	 * @var DateTimeImmutable|null
	 */
	#[ORM\Column(type: 'date', nullable: true)]
	public $lidafdatum;
	#[ORM\Column(type: 'string', enumType: LidStatus::class)]
	public LidStatus $status;
	// geld
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $bankrekening;
	/**
	 * @var boolean|null
	 */
	#[ORM\Column(type: 'boolean', nullable: true)]
	public $machtiging;
	/**
	 * @var boolean|null
	 */
	#[ORM\Column(type: 'boolean', nullable: true, name: 'toestemmingAfschrijven')]
	public $toestemmingAfschrijven;
	// verticale
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $moot;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $verticale;
	/**
	 * @var boolean|null
	 */
	#[ORM\Column(type: 'boolean', nullable: true)]
	public $verticaleleider;
	/**
	 * @var boolean|null
	 */
	#[ORM\Column(type: 'boolean', nullable: true)]
	public $kringcoach;
	// civi-gegevens
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid', nullable: true)]
	public $patroon;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $eetwens;
	/**
	 * @var integer|null
	 */
	#[ORM\Column(type: 'integer', nullable: true)]
	public $corvee_punten;
	/**
	 * @var integer|null
	 */
	#[ORM\Column(type: 'integer', nullable: true)]
	public $corvee_punten_bonus;
	// novitiaat
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'text', nullable: true)]
	public $novitiaat;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'text', nullable: true, name: 'novitiaatBijz')]
	public $novitiaatBijz;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'text', nullable: true)]
	public $medisch;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $startkamp;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true, name: 'matrixPlek')]
	public $matrixPlek;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true, name: 'novietSoort')]
	public $novietSoort;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'text', nullable: true)]
	public $kgb;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'text', nullable: true)]
	public $vrienden;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true, name: 'middelbareSchool')]
	public $middelbareSchool;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $huisarts;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true, name: 'huisartsPlaats')]
	public $huisartsPlaats;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true, name: 'huisartsTelefoon')]
	public $huisartsTelefoon;
	// overig
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true, name: 'profielOpties')]
	public $profielOpties;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $kerk;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $muziek;
	/**
	 * @var string|null
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $zingen;

	/**
	 * @var Account|null
	 */
	#[
		ORM\OneToOne(
		targetEntity: \CsrDelft\entity\security\Account::class,
		mappedBy: 'profiel'
	)
	]
	public $account;

	#[
		ORM\OneToMany(
			targetEntity: LidToestemming::class,
			mappedBy: 'profiel'
		)
	]
	/** @var Collection<int, LidToestemming> */
	public Collection $toestemmingen;

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

	public function getUUID(): string
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

	public function getAdres(): string
	{
		return $this->adres .
			', ' .
			$this->postcode .
			', ' .
			$this->woonplaats .
			', ' .
			$this->land;
	}

	public function getAdresOuders()
	{
		return $this->o_adres .
			', ' .
			$this->o_postcode .
			', ' .
			$this->o_woonplaats .
			', ' .
			$this->o_land;
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
			substr(
				DateUtil::dateFormatIntl($this->gebdatum, DateUtil::DATE_FORMAT),
				5,
				5
			) === date('m-d');
	}

	/**
	 * Vervormt kommagescheiden opties naar lijst,
	 * voegt lichting toe en voegt verjaardag toe indien van toepassing.
	 */
	public function getProfielOpties()
	{
		$opties = $this->profielOpties
			? array_map(
				fn($a) => trim((string) $a),
				explode(',', $this->profielOpties)
			)
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

	public function isHeledag(): bool
	{
		return true;
	}

	public function getTitel(): string
	{
		return $this->getNaam('civitas');
	}

	public function getBeschrijving(): string
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

	public function getLocatie(): string
	{
		return $this->getAdres();
	}

	public function getUrl(): string
	{
		return '/profiel/' . $this->uid;
	}

	// TODO: Dit moet anders. Pasfoto mag weg
	public function getLink($vorm = 'civitas'): string
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
			if ($vorm === 'pasfoto') {
				return $this->getPasfotoTag();
			}
			if ($vorm === 'pasfoto.vierkant') {
				return $this->getPasfotoTag('', 'vierkant');
			}
			return $this->getNaam();
		}
		$naam = $this->getNaam($vorm);
		if ($vorm === 'pasfoto') {
			$naam = $this->getPasfotoTag();
		} elseif ($vorm === 'pasfoto.vierkant') {
			$naam = $this->getPasfotoTag('', 'vierkant');
		} elseif ($this->lidjaar === 2013) {
			$naam = CsrBB::parse('[neuzen]' . $naam . '[/neuzen]');
		}
		if (
			$vorm !== 'pasfoto' && $vorm !== 'pasfoto.vierkant' &&
			InstellingUtil::lid_instelling('layout', 'visitekaartjes') == 'ja'
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
			htmlspecialchars($this->status->value) .
			'" data-lid="' .
			$this->uid .
			'" data-lid-naam="' .
			$this->getNaam($vorm) .
			'">';
		if (
			$vorm !== 'pasfoto' && $vorm !== 'pasfoto.vierkant' &&
			InstellingUtil::lid_instelling('layout', 'visitekaartjes') == 'ja'
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
				htmlspecialchars($this->status->value) .
				'">' .
				$naam .
				'</a></span>';
		} elseif ($vorm === 'leeg') {
			$twig = ContainerFacade::getContainer()->get('twig');

			return $twig->render('profiel/kaartje.html.twig', ['profiel' => $this]);
		}

		return $l . $naam . '</a>';
	}

	public function isTransparant(): bool
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
			$vorm = InstellingUtil::lid_instelling('forum', 'naamWeergave');
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
				if ($this->status === LidStatus::Noviet){
					if ($this->uid==="2404"){
						$naam = 'Feut ' . $this->voornaam;
					} else{
						$naam = 'Noviet ' . $this->voornaam;
					}
					if (!empty($this->postfix)) {
						$naam .= ' ' . $this->postfix;
					}
				} elseif ($this->isLid() || $this->isOudlid()) {
					// voor novieten is het Dhr./ Mevr.
					if (LoginService::getProfiel()->status === LidStatus::Noviet) {
						$naam = $this->geslacht === Geslacht::Vrouw ? 'Mevr. ' : 'Dhr. ';
					} else {
						$naam = $this->geslacht === Geslacht::Vrouw ? 'Ama. ' : 'Am. ';
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
						$naam .= ' ' . $this->status->getChar();
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
						$naam .= ' ' . $this->status->getChar();
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

	public function pasfoto(): string {
		return "/profiel/pasfoto/$this->uid.jpg";
	}

	public function pasfotoVierkant(): string {
		return "/profiel/pasfoto/$this->uid.vierkant.jpg";
	}

	/**
	 * Kijkt of er een pasfoto voor het gegeven uid is, en geef die terug.
	 * Geef anders een standaard-plaatje terug.
	 *
	 * @deprecated gebruik Profiel::pasfoto
	 * @param string $vorm
	 * @return string
	 */
	public function getPasfotoPath($vorm = 'user')
	{
		if (in_array($vorm, ['Duckstad', 'vierkant'])) {
			return "/profiel/pasfoto/$this->uid.$vorm.jpg";
		}

		return "/profiel/pasfoto/$this->uid.jpg";
	}

	public function getPasfotoInternalPath($vorm = 'user')
	{
		$path = null;
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

	public function getPasfotoTag($cssClass = '', $vorm = 'user')
	{
		return '<img class="pasfoto ' .
			htmlspecialchars((string) $cssClass) .
			'" src="' .
			$this->getPasfotoPath($vorm) .
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
	 */
	#[ORM\ManyToOne(targetEntity: \Profiel::class, inversedBy: 'kinderen')]
	#[
		ORM\JoinColumn(name: 'patroon', referencedColumnName: 'uid', nullable: true)
	]
	private $patroonProfiel;

	public function getPatroonProfiel()
	{
		try {
			$patroonProfiel = $this->patroonProfiel;
			if ($patroonProfiel instanceof Proxy) {
				$patroonProfiel->__load();
			}
			return $patroonProfiel;
		} catch (EntityNotFoundException) {
			return null;
		}
	}

	/**
	 * @var Profiel[]|ArrayCollection
	 */
	#[ORM\OneToMany(targetEntity: \Profiel::class, mappedBy: 'patroonProfiel')]
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
		return $this->status->isLidLike();
	}

	public function isOudlid()
	{
		return $this->status->isOudlidLike();
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

	public function getDataTableColumn(): DataTableColumn
	{
		return new DataTableColumn(
			$this->getLink('volledig'),
			$this->achternaam,
			$this->getNaam('volledig')
		);
	}

	// FIXME(#1231): Wordt gebruikt in DoctrineEntityField voor Formlogica, die verwacht dat alle velden null kunnen zijn.
	public function getId(): ?string
	{
		return $this->uid;
	}

	public function getWeergave(): string
	{
		return $this->achternaam ? $this->getNaam('volledig') : '';
	}

	public function getChar(): string
	{
		return $this->status->getChar();
	}

	public function getLidStatusDescription(): string
	{
		return $this->status->getDescription();
	}

	public function getLeeftijd(): int
	{
		return $this->gebdatum->diff(date_create_immutable())->y;
	}
}
