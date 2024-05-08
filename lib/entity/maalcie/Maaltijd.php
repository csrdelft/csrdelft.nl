<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\Eisen;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\DisplayEntity;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

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
 */
#[ORM\Table('mlt_maaltijden')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\maalcie\MaaltijdenRepository::class)]
class Maaltijd implements Agendeerbaar, DisplayEntity
{
	/**
  * @var integer
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $maaltijd_id;
	/**
  * @var integer|null
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'integer', nullable: true)]
 public $mlt_repetitie_id;
	/**
  * @var MaaltijdRepetitie|null
  */
 #[ORM\JoinColumn(name: 'mlt_repetitie_id', referencedColumnName: 'mlt_repetitie_id', nullable: true)]
 #[ORM\ManyToOne(targetEntity: \MaaltijdRepetitie::class)]
 public $repetitie;
	/**
  * @var integer
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'integer')]
 public $product_id;
	/**
  * @var CiviProduct
  */
 #[ORM\ManyToOne(targetEntity: \CsrDelft\entity\fiscaat\CiviProduct::class)]
 public $product;
	/**
  * @var string
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'string')]
 public $titel;
	/**
  * @var int
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'integer')]
 public $aanmeld_limiet;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'date')]
 public $datum;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'time')]
 public $tijd;
	/**
  * @var bool
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'boolean')]
 public $gesloten = false;
	/**
  * @var DateTimeInterface|null
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'datetime', nullable: true)]
 public $laatst_gesloten;
	/**
  * @var bool
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'boolean')]
 public $verwijderd = false;
	/**
  * @var string|null
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $aanmeld_filter;
	/**
  * @var string|null
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'text', nullable: true)]
 public $omschrijving;
	/**
	 * @var integer
	 */
	public $aantal_aanmeldingen;
	/**
  * @var bool
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'boolean')]
 public $verwerkt = false;
	/**
  * @var MaaltijdAanmelding[]|ArrayCollection
  */
 #[ORM\OneToMany(targetEntity: \MaaltijdAanmelding::class, mappedBy: 'maaltijd')]
 public $aanmeldingen;
	/**
	 * De taak die rechten geeft voor het bekijken en sluiten van de maaltijd(-lijst)
	 * @var CorveeTaak
	 */
	public $maaltijdcorvee;

	public function __construct()
	{
		$this->aanmeldingen = new ArrayCollection();
	}

	public function getPrijsFloat()
	{
		return (float) $this->getPrijs() / 100.0;
	}

	/**
	 * @return integer
	 * @Serializer\Groups("datatable")
	 */
	public function getPrijs()
	{
		return $this->product->getPrijsInt();
	}

	public function getIsAangemeld($uid)
	{
		return $this->aanmeldingen->matching(Eisen::voorGebruiker($uid))->count() ==
			1;
	}

	/**
	 * @return int
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("aantal_aanmeldingen")
	 */
	public function getAantalAanmeldingen(): int
	{
		$aantalAanmeldingen = 0;
		foreach ($this->aanmeldingen as $aanmelding) {
			$aantalAanmeldingen += 1 + $aanmelding->aantal_gasten;
		}

		return $aantalAanmeldingen;
	}

	/**
	 * Bereken de marge in verband met niet aangemelde gasten.
	 *
	 * @return int
	 */
	public function getMarge()
	{
		$aantal = $this->getAantalAanmeldingen();
		$marge = floor(
			$aantal /
				floatval(
					InstellingUtil::instelling('maaltijden', 'marge_gasten_verhouding')
				)
		);
		$min = intval(InstellingUtil::instelling('maaltijden', 'marge_gasten_min'));
		if ($marge < $min) {
			$marge = $min;
		}
		$max = intval(InstellingUtil::instelling('maaltijden', 'marge_gasten_max'));
		if ($marge > $max) {
			$marge = $max;
		}
		return $marge;
	}

	/**
	 * Bereken het budget voor deze maaltijd.
	 *
	 * @return integer
	 */
	public function getBudget()
	{
		$budget = $this->getAantalAanmeldingen() + $this->getMarge();
		$budget *=
			$this->getPrijs() -
			intval(InstellingUtil::instelling('maaltijden', 'budget_maalcie'));
		return $budget;
	}

	/**
	 * Vind corveetaken van gegeven functie bij deze maaltijd
	 *
	 * @param $functieID int ID van de functie
	 * @return CorveeTaak[]
	 */
	public function getCorveeTaken($functieID)
	{
		return ContainerFacade::getContainer()
			->get(CorveeTakenRepository::class)
			->findBy([
				'corveeFunctie' => $functieID,
				'maaltijd_id' => $this->maaltijd_id,
				'verwijderd' => false,
			]);
	}

	// Agendeerbaar ############################################################

	public function getTitel()
	{
		return $this->titel;
	}

	public function getBeginMoment(): DateTimeImmutable
	{
		return $this->getMoment();
	}

	public function getEindMoment(): DateTimeImmutable
	{
		return $this->getBeginMoment()->add(new \DateInterval('PT1H30M'));
	}

	public function getBeschrijving()
	{
		return 'Maaltijd met ' .
			$this->getAantalAanmeldingen() .
			' eters (max. ' .
			$this->getAanmeldLimiet() .
			')';
	}

	public function getLocatie()
	{
		return 'C.S.R. Delft';
	}

	public function getUrl()
	{
		return '/maaltijden';
	}

	public function isHeledag()
	{
		return false;
	}

	public function isTransparant()
	{
		// Toon als transparant (vrij) als lid dat wil of lid niet ingeketzt is
		return InstellingUtil::lid_instelling('agenda', 'transparantICal') ===
			'ja' || !$this->getIsAangemeld(LoginService::getUid());
	}

	// Controller ############################################################

	/**
	 * Deze functie bepaalt of iemand de maaltijd(-lijst) mag zien.
	 *
	 * @param string $uid
	 * @return boolean
	 * @throws CsrException
	 */
	public function magBekijken($uid)
	{
		if (!isset($this->maaltijdcorvee)) {
			// Zoek op datum, want er kunnen meerdere maaltijden op 1 dag zijn terwijl er maar 1 kookploeg is.
			// Ook hoeft een taak niet per se gekoppeld te zijn aan een maaltijd (maximaal aan 1 maaltijd).
			/** @var CorveeTaak $taken */
			$corveeTakenRepository = ContainerFacade::getContainer()->get(
				CorveeTakenRepository::class
			);
			$taken = $corveeTakenRepository->getTakenVoorAgenda(
				$this->getMoment(),
				$this->getMoment()
			);
			foreach ($taken as $taak) {
				if (
					$taak->profiel &&
					$taak->profiel->uid === $uid &&
					$taak->maaltijd_id !== null
				) {
					// checken op gekoppelde maaltijd (zie hierboven)
					$this->maaltijdcorvee = $taak; // de taak die toegang geeft tot de maaltijdlijst
					return true;
				}
			}
			$this->maaltijdcorvee = null;
		}
		return $this->maaltijdcorvee !== null;
	}

	/**
	 * Deze functie bepaalt of iemand deze maaltijd mag sluiten of niet.
	 *
	 * @param string $uid
	 * @return boolean
	 * @throws CsrException
	 */
	public function magSluiten($uid)
	{
		return $this->magBekijken($uid) &&
			$this->maaltijdcorvee->corveeFunctie->maaltijden_sluiten; // mag iemand met deze functie maaltijden sluiten?
	}

	/**
	 * @return string
	 * @Serializer\SerializedName("repetitie_naam")
	 * @Serializer\Groups("datatable")
	 */
	public function getRepetitieNaam()
	{
		return $this->repetitie ? $this->repetitie->standaard_titel : null;
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("tijd")
	 */
	public function getDataTableTijd()
	{
		return DateUtil::dateFormatIntl($this->tijd, DateUtil::TIME_FORMAT);
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("datum")
	 */
	public function getDataTableDatum()
	{
		return DateUtil::dateFormatIntl($this->datum, DateUtil::DATE_FORMAT);
	}

	public function getAanmeldLimiet()
	{
		return $this->aanmeld_limiet;
	}

	/**
	 * @return int
	 * @Serializer\Groups("datatable-fiscaat")
	 */
	public function getTotaal()
	{
		return $this->getAantalAanmeldingen() + $this->getPrijs();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("UUID")
	 */
	public function getUUID()
	{
		return $this->maaltijd_id . '@maaltijd.csrdelft.nl';
	}

	public function getMoment()
	{
		return $this->datum->setTime(
			$this->tijd->format('H'),
			$this->tijd->format('i'),
			$this->tijd->format('s')
		);
	}

	public function getId()
	{
		return $this->maaltijd_id;
	}

	public function getWeergave(): string
	{
		if ($this->datum) {
			return $this->titel .
				' op ' .
				DateUtil::dateFormatIntl($this->datum, DateUtil::DATE_FORMAT) .
				' om ' .
				DateUtil::dateFormatIntl($this->getMoment(), DateUtil::TIME_FORMAT);
		} else {
			return $this->titel ?? '';
		}
	}

	public function getAanmelding(Profiel $profiel)
	{
		return $this->aanmeldingen
			->matching(Eisen::voorGebruiker($profiel->uid))
			->first();
	}
}
