<?php

namespace CsrDelft\model\entity\agenda;

use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * AgendaItem.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * AgendaItems worden door de agenda getoont samen met andere Agendeerbare dingen.
 */
class AgendaItem extends PersistentEntity implements Agendeerbaar {

	/**
	 * Primary key
	 * @var int
	 */
	public $item_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Beschrijving
	 * @var string
	 */
	public $beschrijving;
	/**
	 * DateTime begin
	 * @var string
	 */
	public $begin_moment;
	/**
	 * DateTime eind
	 * @var string
	 */
	public $eind_moment;
	/**
	 * Permissie voor tonen
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Locatie
	 * @var string
	 */
	public $locatie;
	/**
	 * Link
	 * @var string
	 */
	public $link;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'item_id' => array(T::Integer, false, 'auto_increment'),
		'titel' => array(T::String),
		'beschrijving' => array(T::Text, true),
		'begin_moment' => array(T::DateTime),
		'eind_moment' => array(T::DateTime),
		'rechten_bekijken' => array(T::String),
		'locatie' => array(T::String, true),
		'link' => array(T::String, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('item_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'agenda';

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
		return $this->titel;
	}

	public function getBeschrijving() {
		return $this->beschrijving;
	}

	public function getLocatie() {
		return $this->locatie;
	}

	public function getUrl() {
		return $this->link;
	}

	public function isHeledag() {
		$begin = date('H:i', $this->getBeginMoment());
		$eind = date('H:i', $this->getEindMoment());
		return $begin == '00:00' AND ($eind == '23:59' OR $eind == '00:00');
	}

	public function magBekijken($ical = false) {
		$auth = ($ical ? AuthenticationMethod::getTypeOptions() : null);
		return LoginModel::mag($this->rechten_bekijken, $auth);
	}

	public function magBeheren($ical = false) {
		$auth = ($ical ? AuthenticationMethod::getTypeOptions() : null);
		if (LoginModel::mag('P_AGENDA_MOD', $auth)) {
			return true;
		}
		$verticale = 'verticale:' . LoginModel::getProfiel()->verticale;
		if ($this->rechten_bekijken === $verticale AND LoginModel::getProfiel()->verticaleleider) {
			return true;
		}
		return false;
	}
}
