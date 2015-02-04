<?php

/**
 * Bijbelrooster.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het bijbelrooster wordt door de agenda getoont samen met andere Agendeerbare dingen.
 */
class Bijbelrooster extends PersistentEntity implements Agendeerbaar {

	/**
	 * Primary key
	 * @var string
	 */
	public $dag;
	/**
	 * Bijbelstukje
	 * @var string
	 */
	public $stukje;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'dag'	 => array(T::DateTime),
		'stukje' => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('dag');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'bijbelrooster';

	public function getBeginMoment() {
		return strtotime($this->dag);
	}

	public function getEindMoment() {
		return strtotime($this->dag) + 3600;
	}

	public function getTitel() {
		return $this->stukje;
	}

	public function getBeschrijving() {
		return null;
	}

	public function getLocatie() {
		return null;
	}

	public function getLink($tag = false) {
		return CsrBB::getBijbelLink($this->stukje, null, $tag);
	}

	public function isHeledag() {
		return true;
	}

}
