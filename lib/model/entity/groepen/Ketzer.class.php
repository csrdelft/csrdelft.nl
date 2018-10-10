<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\KetzerSelectorsModel;
use CsrDelft\model\groepen\leden\KetzerDeelnemersModel;
use CsrDelft\Orm\Entity\T;

/**
 * Ketzer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ketzer is een aanmeldbare groep.
 *
 */
class Ketzer extends AbstractGroep {

	const leden = KetzerDeelnemersModel::class;

	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var string
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var string
	 */
	public $aanmelden_tot;
	/**
	 * Datum en tijd aanmelding bewerken toegestaan
	 * @var string
	 */
	public $bewerken_tot;
	/**
	 * Datum en tijd afmelden toegestaan
	 * @var string
	 */
	public $afmelden_tot;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'aanmeld_limiet' => array(T::Integer, true),
		'aanmelden_vanaf' => array(T::DateTime),
		'aanmelden_tot' => array(T::DateTime),
		'bewerken_tot' => array(T::DateTime, true),
		'afmelden_tot' => array(T::DateTime, true)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzers';

	public function getUrl() {
		return '/groepen/ketzers/' . $this->id . '/';
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return KetzerSelector[]
	 */
	public function getSelectors() {
		return KetzerSelectorsModel::instance()->getSelectorsVoorKetzer($this);
	}

	/**
	 * Has permission for action?
	 *
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {
			case AccessAction::Aanmelden:
				// Controleer maximum leden
				if (isset($this->aanmeld_limiet) AND $this->aantalLeden() >= $this->aanmeld_limiet) {
					return false;
				}
				// Controleer aanmeldperiode
				if (time() > strtotime($this->aanmelden_tot) OR time() < strtotime($this->aanmelden_vanaf)) {
					return false;
				}
				break;

			case AccessAction::Bewerken:
				// Controleer bewerkperiode
				if (time() > strtotime($this->bewerken_tot)) {
					return false;
				}
				break;

			case AccessAction::Afmelden:
				// Controleer afmeldperiode
				if (time() > strtotime($this->afmelden_tot)) {
					return false;
				}
				break;
		}
		return parent::mag($action, $allowedAuthenticationMethods);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods=null) {
		switch ($action) {

			case AccessAction::Aanmaken:
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return true;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

}
