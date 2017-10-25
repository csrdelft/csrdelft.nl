<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\leden\ActiviteitDeelnemersModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;


/**
 * Activiteit.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Activiteit extends Ketzer implements Agendeerbaar {

	const leden = ActiviteitDeelnemersModel::class;

	/**
	 * Intern / Extern / SjaarsActie / etc.
	 * @var ActiviteitSoort
	 */
	public $soort;
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Locatie
	 * @var string
	 */
	public $locatie;
	/**
	 * Tonen in agenda
	 * @var boolean
	 */
	public $in_agenda;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, ActiviteitSoort::class),
		'rechten_aanmelden' => array(T::String, true),
		'locatie' => array(T::String, true),
		'in_agenda' => array(T::Boolean)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'activiteiten';

	public function getUrl() {
		return '/groepen/activiteiten/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 *
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmelden:
				if (!empty($this->rechten_aanmelden) AND !LoginModel::mag($this->rechten_aanmelden)) {
					return false;
				}
				break;
		}
		return parent::mag($action);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $soort = null) {
		switch ($soort) {

			case ActiviteitSoort::OWee:
				if (LoginModel::mag('commissie:OWeeCie')) {
					return true;
				}
				break;

			case ActiviteitSoort::Dies:
				if (LoginModel::mag('commissie:DiesCie')) {
					return true;
				}
				break;

			case ActiviteitSoort::Lustrum:
				if (LoginModel::mag('commissie:LustrumCie')) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action);
	}

	// Agendeerbaar:

	public function getBeginMoment() {
		return strtotime($this->begin_moment);
	}

	public function getEindMoment() {
		if ($this->eind_moment AND $this->eind_moment !== $this->begin_moment) {
			return strtotime($this->eind_moment);
		}
		return $this->getBeginMoment() + 1800;
	}

	public function getTitel() {
		return $this->naam;
	}

	public function getBeschrijving() {
		return $this->samenvatting;
	}

	public function getLocatie() {
		return $this->locatie;
	}

	public function getLink() {
		return $this->getUrl();
	}

	public function isHeledag() {
		$begin = date('H:i', $this->getBeginMoment());
		$eind = date('H:i', $this->getEindMoment());
		return $begin == '00:00' AND ($eind == '23:59' OR $eind == '00:00');
	}

}
