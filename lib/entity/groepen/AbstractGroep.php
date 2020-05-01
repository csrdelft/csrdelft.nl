<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Eisen;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\AbstractGroepLedenRepository;
use CsrDelft\service\GroepenService;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
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
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var DateTimeImmutable
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
	 * @return AbstractGroepLid[]|ArrayCollection
	 */
	abstract public function getLeden();

	/**
	 * @return string|AbstractGroepLid
	 */
	abstract public function getLidType();

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return AbstractGroepLid
	 */
	public function getLid($uid) {
		return $this->getLeden()->matching(Eisen::voorGebruiker($uid))->first();
	}

	public function aantalLeden() {
		return $this->getLeden()->count();
	}

	public function getStatistieken() {
		return GroepenService::getStatistieken($this);
	}

	public function getFamilieSuggesties() {
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		$tableName = $em->getClassMetadata($this)->getTableName();

		return ContainerFacade::getContainer()->get(Database::class)->sqlSelect(['DISTINCT familie'], $tableName)->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = [];
		} elseif ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getTypeOptions();
		} else {
			$suggesties = array_unique($this->getLeden()->map(function(AbstractGroepLid $lid) { return $lid->opmerking; })->toArray());
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

		$aangemeld = $this->getLid(LoginModel::getUid()) != null;
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
