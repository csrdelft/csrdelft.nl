<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Eisen;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\groepen\enum\CommissieFunctie;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\repository\GroepRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="groep_type", type="string")
 * @ORM\DiscriminatorMap({
 *   "groep" = "Groep",
 *   "activiteit" = "Activiteit",
 *   "bestuur" = "Bestuur",
 *   "commissie" = "Commissie",
 *   "ketzer" = "Ketzer",
 *   "kring" = "Kring",
 *   "lichting" = "Lichting",
 *   "ondervereniging" = "Ondervereniging",
 *   "rechtengroep" = "RechtenGroep",
 *   "verticale" = "Verticale",
 *   "werkgroep" = "Werkgroep",
 *   "woonoord" = "Woonoord",
 * })
 * @ORM\Table("groep", indexes={
 *   @ORM\Index(columns={"in_agenda"}),
 *   @ORM\Index(columns={"familie"}),
 *   @ORM\Index(columns={"begin_moment"}),
 *   @ORM\Index(columns={"huis_status"}),
 *   @ORM\Index(columns={"ondervereniging_status"}),
 *   @ORM\Index(columns={"activiteit_soort"}),
 *   @ORM\Index(columns={"commissie_soort"}),
 *   @ORM\Index(columns={"eetplan"}),
 *   @ORM\Index(columns={"kring_nummer"}),
 *   @ORM\Index(columns={"verticale"}),
 *   @ORM\Index(columns={"groep_type"}),
 * })
 */
class Groep implements DataTableEntry, DisplayEntity
{
	/**
	 * Primary key, groter dan 3000 in de database
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $id;
	/**
	 * Oude ID, uniek voor type groep, kleiner dan 3000 in de database (sorry)
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public $oudId;

	/**
	 * Naam
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $naam;
	/**
	 * Naam voor opvolging
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $familie;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 * @ORM\Column(type="enumGroepStatus")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $status;
	/**
	 * Korte omschrijving
	 * @var string
	 * @ORM\Column(type="text")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $samenvatting;
	/**
	 * Lange omschrijving
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $omschrijving;
	/**
	 * Serialized keuzelijst(en)
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $keuzelijst;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="maker_uid", referencedColumnName="uid", nullable=false)
	 */
	public $maker;
	/**
	 * @var GroepVersie
	 * @ORM\Column(type="enumGroepVersie")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $versie;
	/**
	 * @var GroepKeuze[]
	 * @ORM\Column(type="groepkeuze", nullable=true)
	 * @Serializer\Groups("vue")
	 */
	public $keuzelijst2 = [];
	/**
	 * Gebruik @see Groep::getLeden om leden op te vragen.
	 * @var GroepLid[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="GroepLid", mappedBy="groep")
	 * @ORM\OrderBy({"lidSinds"="ASC"})
	 * @ORM\JoinColumn(name="groep_id", referencedColumnName="id")
	 */
	protected $leden;

	public function __construct()
	{
		$this->versie = GroepVersie::V1();
		$this->leden = new ArrayCollection();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("detailSource")
	 */
	public function getDetailSource()
	{
		return $this->getUrl() . '/leden';
	}

	/**
	 * De URL van de groep
	 * @return string
	 */
	public function getUrl()
	{
		return '/groepen/groep/' . $this->id;
	}

	public function aantalLeden()
	{
		return $this->getLeden()->count();
	}

	/**
	 * Maak het mogelijk om leden te 'faken', zie verticale/lichting
	 * @return GroepLid[]|ArrayCollection
	 * @Serializer\Groups("vue")
	 */
	public function getLeden()
	{
		return $this->leden;
	}

	public function getLedenOpAchternaamGesorteerd()
	{
		$leden = $this->getLeden();
		try {
			$iterator = $leden->getIterator();
			$iterator->uasort(function (GroepLid $a, GroepLid $b) {
				return strcmp($a->profiel->achternaam, $b->profiel->achternaam) ?:
					strnatcmp($a->uid, $b->uid);
			});
		} catch (Exception $e) {
			return $leden;
		}
		return new ArrayCollection(iterator_to_array($iterator));
	}

	public function getFamilieSuggesties()
	{
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		/** @var GroepRepository $repo */
		$repo = $em->getRepository(get_class($this));

		$result = $repo
			->createQueryBuilder('g')
			->select('DISTINCT g.familie')
			->getQuery()
			->getScalarResult();

		return array_map(function ($e) {
			return $e['familie'];
		}, $result);
	}

	public function getOpmerkingSuggesties()
	{
		if (isset($this->keuzelijst)) {
			$suggesties = [];
		} elseif ($this instanceof Commissie || $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getEnumValues();
		} else {
			$suggesties = array_unique(
				$this->getLeden()
					->map(function (GroepLid $lid) {
						return $lid->opmerking;
					})
					->toArray()
			);
		}
		return $suggesties;
	}

	public function magWijzigen()
	{
		return $this->mag(AccessAction::Wijzigen());
	}

	/**
	 * Has permission for action?
	 *
	 * @param AccessAction $action
	 * @param array|null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag(AccessAction $action)
	{
		if (!LoginService::mag(P_LOGGED_IN)) {
			return false;
		}

		if (
			in_array(GroepAanmeldLimiet::class, class_uses($this)) &&
			!$this->magAanmeldLimiet($action)
		) {
			return false;
		}

		if (
			in_array(GroepAanmeldMoment::class, class_uses($this)) &&
			!$this->magAanmeldMoment($action)
		) {
			return false;
		}

		if (
			in_array(GroepAanmeldRechten::class, class_uses($this)) &&
			!$this->magAanmeldRechten($action)
		) {
			return false;
		}

		$aangemeld = $this->getLid(LoginService::getUid()) != null;
		switch ($action) {
			case AccessAction::Aanmelden():
				if ($aangemeld) {
					return false;
				}
				break;

			case AccessAction::Bewerken():
			case AccessAction::Afmelden():
				if (!$aangemeld) {
					return false;
				}
				break;

			default:
				// Maker van groep mag alles
				if ($this->maker->uid === LoginService::getUid()) {
					return true;
				}
				break;
		}
		return static::magAlgemeen($action);
	}

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return GroepLid|null
	 */
	public function getLid($uid)
	{
		if ($this->getLeden() == null) {
			return null;
		}

		return $this->getLeden()
			->matching(Eisen::voorGebruiker($uid))
			->first();
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param null $soort
	 * @return boolean
	 */
	public static function magAlgemeen(AccessAction $action, $soort = null)
	{
		switch ($action) {
			case AccessAction::Bekijken():
				return LoginService::mag(P_LEDEN_READ);

			// Voorkom dat moderators overal een normale aanmeldknop krijgen
			case AccessAction::Aanmelden():
			case AccessAction::Bewerken():
			case AccessAction::Afmelden():
				return false;
		}
		// Moderators mogen alles
		return LoginService::mag(P_LEDEN_MOD . ',groep:P_GROEP:_MOD');
	}

	/**
	 * Controleer of keuzes overeen komen.
	 *
	 * @param GroepKeuzeSelectie[] $keuzes
	 * @return bool
	 */
	public function valideerOpmerking(array $keuzes)
	{
		$correct = [];
		foreach ($keuzes as $keuze) {
			foreach ($this->keuzelijst2 as $optie) {
				if ($optie->naam == $keuze->naam && !in_array($keuze, $correct)) {
					$correct[] = $keuze;
				}
			}
		}

		return count($keuzes) == count($correct);
	}

	/**
	 * @return string|null
	 * @Serializer\Groups("vue")
	 */
	public function getSamenvattingHtml()
	{
		return CsrBB::parse($this->samenvatting);
	}

	public function getUUID()
	{
		return $this->id . '@' . short_class($this) . '.csrdelft.nl';
	}

	public function getId()
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->naam ?? '';
	}
}
