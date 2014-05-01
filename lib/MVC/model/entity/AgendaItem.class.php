<?php

require_once 'MVC/model/Agendeerbaar.interface.php';

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
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'item_id' => array('int', 11, false, null, 'auto_increment'),
		'titel' => array('string', 255),
		'beschrijving' => array('text'),
		'begin_moment' => array('datetime'),
		'eind_moment' => array('datetime'),
		'rechten_bekijken' => array('string', 255),
		'locatie' => array('string', 255, true),
		'link' => array('string', 255, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_keys = array('item_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'agenda';

	public function getUID() {
		return $this->item_id . '@' . static::$table_name . '.csrdelft.nl';
	}

	public function getBeginMoment() {
		return strtotime($this->begin_moment);
	}

	public function getEindMoment() {
		return strtotime($this->eind_moment);
	}

	public function getDuration() {
		return ($this->getEindMoment() - $this->getBeginMoment()) / 60;
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

	public function getLink() {
		return $this->link;
	}

	public function isHeledag() {
		return date('H:i', $this->getBeginMoment()) == '00:00' AND date('H:i', $this->getEindMoment()) == '23:59';
	}

	public function magBekijken() {
		return LoginLid::mag($this->rechten_bekijken);
	}

}
