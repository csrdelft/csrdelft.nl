<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Eisen;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
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
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\maalcie\MaaltijdenRepository")
 * @ORM\Table("mlt_maaltijden")
 */
class Maaltijd implements Agendeerbaar, HeeftAanmeldLimiet, DisplayEntity
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @Serializer\Groups("datatable")
     */
    public $maaltijd_id;
    /**
     * @var integer|null
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Groups("datatable")
     */
    public $mlt_repetitie_id;
    /**
     * @var MaaltijdRepetitie|null
     * @ORM\ManyToOne(targetEntity="MaaltijdRepetitie")
     * @ORM\JoinColumn(name="mlt_repetitie_id", referencedColumnName="mlt_repetitie_id", nullable=true)
     */
    public $repetitie;
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Serializer\Groups("datatable")
     */
    public $product_id;
    /**
     * @var CiviProduct
     * @ORM\ManyToOne(targetEntity="CsrDelft\entity\fiscaat\CiviProduct")
     */
    public $product;
    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups("datatable")
     */
    public $titel;
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serializer\Groups("datatable")
     */
    public $aanmeld_limiet;
    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="date")
     */
    public $datum;
    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="time")
     */
    public $tijd;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Serializer\Groups("datatable")
     */
    public $gesloten = false;
    /**
     * @var DateTimeInterface|null
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups("datatable")
     */
    public $laatst_gesloten;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Serializer\Groups("datatable")
     */
    public $verwijderd = false;
    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups("datatable")
     */
    public $aanmeld_filter;
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups("datatable")
     */
    public $omschrijving;
    /**
     * @var integer
     */
    public $aantal_aanmeldingen;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Serializer\Groups("datatable")
     */
    public $verwerkt = false;
    /**
     * @var MaaltijdAanmelding[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MaaltijdAanmelding", mappedBy="maaltijd")
     */
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
        return (float)$this->getPrijs() / 100.0;
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
        return $this->aanmeldingen->matching(Eisen::voorGebruiker($uid))->count() == 1;
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
     * @return integer
     */
    public function getBudget()
    {
        $budget = $this->getAantalAanmeldingen() + $this->getMarge();
        $budget *= $this->getPrijs() - intval(instelling('maaltijden', 'budget_maalcie'));
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
        return ContainerFacade::getContainer()->get(CorveeTakenRepository::class)->findBy(['corveeFunctie' => $functieID, 'maaltijd_id' => $this->maaltijd_id, 'verwijderd' => false]);
    }

    // Agendeerbaar ############################################################

    public function getTitel()
    {
        return $this->titel;
    }

    public function getBeginMoment()
    {
        return $this->getMoment()->getTimestamp();
    }

    public function getEindMoment()
    {
        return $this->getBeginMoment() + 7200;
    }

    public function getBeschrijving()
    {
        return 'Maaltijd met ' . $this->getAantalAanmeldingen() . ' eters';
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
        return lid_instelling('agenda', 'transparantICal') === 'ja' || !$this->getIsAangemeld(LoginService::getUid());
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
            $corveeTakenRepository = ContainerFacade::getContainer()->get(CorveeTakenRepository::class);
            $taken = $corveeTakenRepository->getTakenVoorAgenda($this->getMoment(), $this->getMoment());
            foreach ($taken as $taak) {
                if ($taak->profiel && $taak->profiel->uid === $uid && $taak->maaltijd_id !== null) { // checken op gekoppelde maaltijd (zie hierboven)
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
        return $this->magBekijken($uid) and $this->maaltijdcorvee->corveeFunctie->maaltijden_sluiten; // mag iemand met deze functie maaltijden sluiten?
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
        return date_format_intl($this->tijd, TIME_FORMAT);
    }

    /**
     * @return string
     * @Serializer\Groups("datatable")
     * @Serializer\SerializedName("datum")
     */
    public function getDataTableDatum()
    {
        return date_format_intl($this->datum, DATE_FORMAT);
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
        return $this->maaltijd_id . "@maaltijd.csrdelft.nl";
    }

    public function getMoment()
    {
        return $this->datum->setTime($this->tijd->format('H'), $this->tijd->format('i'), $this->tijd->format('s'));
    }


    public function getId()
    {
        return $this->maaltijd_id;
    }

    public function getWeergave(): string
    {
        if ($this->datum) {
            return $this->titel . ' op ' . date_format_intl($this->datum, DATE_FORMAT) . ' om ' . date_format_intl($this->getMoment(), TIME_FORMAT);
        } else {
            return $this->titel ?? '';
        }
    }
}
