<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\entity\groepen\GroepVersie;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\ORM\Mapping as ORM;
use PDO;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 * @ORM\MappedSuperclass()
 */
abstract class AbstractGroep {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * Naam
	 * @var string
	 * @ORM\Column(type="stringkey")
	 */
	public $naam;
	/**
	 * Naam voor opvolging
	 * @var string
	 * @ORM\Column(type="stringkey")
	 */
	public $familie;
	/**
	 * Datum en tijd begin
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $eind_moment;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 * @ORM\Column(type="enumgroepstatus")
	 */
	public $status;
	/**
	 * Korte omschrijving
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $samenvatting;
	/**
	 * Lange omschrijving
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	public $omschrijving;
	/**
	 * Serialized keuzelijst(en)
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $keuzelijst;
	/**
	 * Lidnummer van aanmaker
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $maker_uid;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $versie = GroepVersie::V1;
	/**
	 * @var GroepKeuze[]
	 * @ORM\Column(type="groepkeuze", nullable=true)
	 */
	public $keuzelijst2;

	protected static $computed_attributes = [
		'leden' => [T::String],
	];

	/**
	 * De URL van de groep
	 * @return string
	 */
	abstract public function getUrl();

	/**
	 * Model voor leden van deze groep.
	 * @var AbstractGroepLedenRepository
	 */
	const LEDEN = null;

	/**
	 * @return AbstractGroepLedenRepository
	 */
	public static function getLedenModel() {
		return ContainerFacade::getContainer()->get(static::LEDEN);
	}

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return AbstractGroepLid
	 */
	public function getLid($uid) {
		return ContainerFacade::getContainer()->get(static::LEDEN)->get($this, $uid);
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return AbstractGroepLid[]
	 */
	public function getLeden() {
		return static::getLedenModel()->getLedenVoorGroep($this);
	}

	public function aantalLeden() {
		return static::getLedenModel()->count(['groep_id' => $this->id]);
	}

	public function getStatistieken() {
		return static::getLedenModel()->getStatistieken($this);
	}

	public function getFamilieSuggesties() {
		return ContainerFacade::getContainer()->get(Database::class)->sqlSelect(['DISTINCT familie'], $this->getTableName())->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = [];
		} elseif ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getTypeOptions();
		} else {
			$suggesties = ContainerFacade::getContainer()->get(Database::class)->sqlSelect(['DISTINCT opmerking'], static::getLedenModel()->getTableName(), 'groep_id = ?', [$this->id])->fetchAll(PDO::FETCH_COLUMN);
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
		if (!LoginModel::mag(P_LOGGED_IN, $allowedAuthenticationMethods)) {
			return false;
		}
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		$ledenMeta = $em->getClassMetadata(static::getLedenModel()->entityClass);

		$aangemeld = ContainerFacade::getContainer()->get(Database::class)->sqlExists($ledenMeta->getTableName(), 'groep_id = ? AND uid = ?', [$this->id, LoginModel::getUid()]);
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
				if ($this->maker_uid === LoginModel::getUid()) {
					return true;
				}
				break;
		}
		return static::magAlgemeen($action, $allowedAuthenticationMethods);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param array|null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
				return LoginModel::mag(P_LEDEN_READ, $allowedAuthenticationMethods);

			// Voorkom dat moderators overal een normale aanmeldknop krijgen
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return false;
		}
		// Moderators mogen alles
		return LoginModel::mag(P_LEDEN_MOD . ',groep:P_GROEP:_MOD', $allowedAuthenticationMethods);
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

}
