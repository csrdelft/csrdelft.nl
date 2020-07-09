<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\common\Eisen;
use CsrDelft\entity\groepen\enum\CommissieFunctie;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\DisplayEntity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation as Serializer;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 * @ORM\MappedSuperclass()
 */
abstract class AbstractGroep implements DataTableEntry, DisplayEntity {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $id;
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
	 * Datum en tijd begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $eind_moment;
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
	public $keuzelijst2;

	/**
	 * De URL van de groep
	 * @return string
	 */
	abstract public function getUrl();

	/**
	 * @return string|AbstractGroepLid
	 */
	abstract public function getLidType();

	public function __construct() {
		$this->versie = GroepVersie::V1();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("detailSource")
	 */
	public function getDetailSource() {
		return $this->getUrl() . '/leden';
	}

	public function aantalLeden() {
		return $this->getLeden()->count();
	}

	/**
	 * @return AbstractGroepLid[]|ArrayCollection
	 * @Serializer\Groups("vue")
	 */
	abstract public function getLeden();

	public function getLedenOpAchternaamGesorteerd() {
		$leden = $this->getLeden();
		try {
			$iterator = $leden->getIterator();
			$iterator->uasort(function (AbstractGroepLid $a, AbstractGroepLid $b) {
				return strcmp($a->profiel->achternaam, $b->profiel->achternaam) ?: strnatcmp($a->uid, $b->uid);
			});
		} catch (Exception $e) {
			return $leden;
		}
		return new ArrayCollection(iterator_to_array($iterator));
	}

	public function getFamilieSuggesties() {
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		/** @var AbstractGroepenRepository $repo */
		$repo = $em->getRepository(get_class($this));

		$result = $repo->createQueryBuilder('g')
			->select('DISTINCT g.familie')
			->getQuery()->getScalarResult();

		return array_map(function ($e) {
			return $e['familie'];
		}, $result);
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = [];
		} elseif ($this instanceof Commissie or $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getEnumValues();
		} else {
			$suggesties = array_unique($this->getLeden()->map(function (AbstractGroepLid $lid) {
				return $lid->opmerking;
			})->toArray());
		}
		return $suggesties;
	}

	/**
	 * Has permission for action?
	 *
	 * @param string $action
	 * @param array|null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		if (!LoginService::mag(P_LOGGED_IN, $allowedAuthenticationMethods)) {
			return false;
		}

		$aangemeld = $this->getLid(LoginService::getUid()) != null;
		switch ($action) {

			case AccessAction::Aanmelden:
				if ($aangemeld) {
					return false;
				}
				break;

			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
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
		return static::magAlgemeen($action, $allowedAuthenticationMethods);
	}

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return AbstractGroepLid
	 */
	public function getLid($uid) {
		if ($this->getLeden() == null) {
			return null;
		}

		return $this->getLeden()->matching(Eisen::voorGebruiker($uid))->first();
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param array|null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null) {
		switch ($action) {

			case AccessAction::Bekijken:
				return LoginService::mag(P_LEDEN_READ, $allowedAuthenticationMethods);

			// Voorkom dat moderators overal een normale aanmeldknop krijgen
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return false;
		}
		// Moderators mogen alles
		return LoginService::mag(P_LEDEN_MOD . ',groep:P_GROEP:_MOD', $allowedAuthenticationMethods);
	}

	/**
	 * Controleer of keuzes overeen komen.
	 *
	 * @param GroepKeuzeSelectie[] $keuzes
	 * @return bool
	 */
	public function valideerOpmerking(array $keuzes) {
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

	public function getUUID() {
		return $this->id . '@' . short_class($this) . '.csrdelft.nl';
	}

	public function getId() {
		return $this->id;
	}

	public function getWeergave(): string {
		return $this->naam;
	}
}
