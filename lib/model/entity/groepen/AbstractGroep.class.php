<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use PDO;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 */
abstract class AbstractGroep extends PersistentEntity {
	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Naam voor opvolging
	 * @var string
	 */
	public $familie;
	/**
	 * Datum en tijd begin
	 * @var string
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var string
	 */
	public $eind_moment;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	public $status;
	/**
	 * Korte omschrijving
	 * @var string
	 */
	public $samenvatting;
	/**
	 * Lange omschrijving
	 * @var string
	 */
	public $omschrijving;
	/**
	 * Serialized keuzelijst(en)
	 * @var string
	 */
	public $keuzelijst;
	/**
	 * Lidnummer van aanmaker
	 * @var string
	 */
	public $maker_uid;
	public $versie = GroepVersie::V1;
	/**
	 * @var GroepKeuze[]
	 */
	public $keuzelijst2;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'naam' => [T::StringKey],
		'familie' => [T::StringKey],
		'begin_moment' => [T::DateTime],
		'eind_moment' => [T::DateTime, true],
		'status' => [T::Enumeration, false, GroepStatus::class],
		'samenvatting' => [T::Text],
		'omschrijving' => [T::Text, true],
		'keuzelijst' => [T::String, true],
		'maker_uid' => [T::UID],
		'versie' => [T::Enumeration, false, GroepVersie::class],
		'keuzelijst2' => [T::JSON, true, [GroepKeuze::class]],
	];
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = ['id'];

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
	 * @var AbstractGroepLedenModel
	 */
	const LEDEN = null;

	/**
	 * @return AbstractGroepLedenModel
	 */
	public static function getLedenModel() {
		$orm = static::LEDEN;

		return $orm::instance();
	}

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return AbstractGroepLid
	 */
	public function getLid($uid) {
		return (static::LEDEN)::get($this, $uid);
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
		return static::getLedenModel()->count('groep_id = ?', [$this->id]);
	}

	public function getStatistieken() {
		return static::getLedenModel()->getStatistieken($this);
	}

	public function getFamilieSuggesties() {
		return Database::instance()->sqlSelect(['DISTINCT familie'], $this->getTableName())->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = [];
		} elseif ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getTypeOptions();
		} else {
			$suggesties = Database::instance()->sqlSelect(['DISTINCT opmerking'], static::getLedenModel()->getTableName(), 'groep_id = ?', [$this->id])->fetchAll(PDO::FETCH_COLUMN);
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
		$aangemeld = Database::instance()->sqlExists(static::getLedenModel()->getTableName(), 'groep_id = ? AND uid = ?', [$this->id, LoginModel::getUid()]);
		switch ($action) {

			case AccessAction::Aanmelden:
				if ($aangemeld) {
					return false;
				}
				break;

			case AccessAction::Bewerken:
				if (!$aangemeld) {
					return false;
				}
				break;

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
