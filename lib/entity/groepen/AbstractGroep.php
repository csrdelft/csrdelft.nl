<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\common\Eisen;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use PDO;
use Symfony\Component\Serializer\Annotation as Serializer;
use function common\short_class;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 * @ORM\MappedSuperclass()
 */
abstract class AbstractGroep implements DataTableEntry {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("datatable")
	 */
	public $id;
	/**
	 * Naam
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @Serializer\Groups("datatable")
	 */
	public $naam;
	/**
	 * Naam voor opvolging
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @Serializer\Groups("datatable")
	 */
	public $familie;
	/**
	 * Datum en tijd begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("datatable")
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("datatable")
	 */
	public $eind_moment;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 * @ORM\Column(type="enumgroepstatus")
	 * @Serializer\Groups("datatable")
	 */
	public $status;
	/**
	 * Korte omschrijving
	 * @var string
	 * @ORM\Column(type="text")
	 * @Serializer\Groups("datatable")
	 */
	public $samenvatting;
	/**
	 * Lange omschrijving
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 * @Serializer\Groups("datatable")
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
	 * @Serializer\Groups("datatable")
	 */
	public $versie = GroepVersie::V1;
	/**
	 * @var GroepKeuze[]
	 * @ORM\Column(type="groepkeuze", nullable=true)
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
	 */
	abstract public function getLeden();

	public function getFamilieSuggesties() {
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		$tableName = $em->getClassMetadata(get_class($this))->getTableName();

		return ContainerFacade::getContainer()->get(Database::class)->sqlSelect(['DISTINCT familie'], $tableName)->fetchAll(PDO::FETCH_COLUMN);
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

	public function getUUID() {
		return $this->id . '@' . short_class($this) . '.csrdelft.nl';
	}
}
