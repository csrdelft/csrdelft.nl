<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use PDO;


/**
 * AbstractGroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep met leden.
 */
abstract class AbstractGroep extends PersistentEntity {

	const leden = 'Error';

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
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'naam' => array(T::StringKey),
		'familie' => array(T::StringKey),
		'begin_moment' => array(T::DateTime),
		'eind_moment' => array(T::DateTime, true),
		'status' => array(T::Enumeration, false, GroepStatus::class),
		'samenvatting' => array(T::Text),
		'omschrijving' => array(T::Text, true),
		'keuzelijst' => array(T::String, true),
		'maker_uid' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');

	/**
	 * De URL van de groep
	 * @return string
	 */
	abstract public function getUrl();

	/**
	 * Is lid van deze groep?
	 *
	 * @param string $uid
	 * @return AbstractGroepLid
	 */
	public function getLid($uid) {
		$leden = static::leden;
		return $leden::get($this, $uid);
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return AbstractGroepLid[]
	 */
	public function getLeden() {
		$leden = static::leden;
		return $leden::instance()->getLedenVoorGroep($this);
	}

	public function aantalLeden() {
		$leden = static::leden;
		return $leden::instance()->count('groep_id = ?', array($this->id));
	}

	public function getStatistieken() {
		$leden = static::leden;
		return $leden::instance()->getStatistieken($this);
	}

	public function getFamilieSuggesties() {
		return Database::instance()->sqlSelect(array('DISTINCT familie'), $this->getTableName())->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = array();
		} elseif ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getTypeOptions();
		} else {
			$leden = static::leden;
			$suggesties = Database::instance()->sqlSelect(array('DISTINCT opmerking'), $leden::instance()->getTableName(), 'groep_id = ?', array($this->id))->fetchAll(PDO::FETCH_COLUMN);
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
		if (!LoginModel::mag('P_LOGGED_IN', $allowedAuthenticationMethods)) {
			return false;
		}
		$leden = static::leden;
		$aangemeld = Database::instance()->sqlExists($leden::instance()->getTableName(), 'groep_id = ? AND uid = ?', array($this->id, LoginModel::getUid()));
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
				return LoginModel::mag('P_LEDEN_READ', $allowedAuthenticationMethods);

			// Voorkom dat moderators overal een normale aanmeldknop krijgen
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return false;
		}
		// Moderators mogen alles
		return LoginModel::mag('P_LEDEN_MOD,groep:P_GROEP:_MOD', $allowedAuthenticationMethods);
	}

}
