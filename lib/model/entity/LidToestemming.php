<?php

namespace CsrDelft\model\entity;

use CsrDelft\model\instellingen\LidToestemmingModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\T;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 *
 * Een LidToestemming beschrijft een Instelling per Lid.
 */
class LidToestemming extends Instelling {
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid' => array(T::UID),
		'module' => array(T::StringKey),
		'instelling_id' => array(T::StringKey),
		'waarde' => array(T::Text)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('module', 'instelling_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lidtoestemmingen';
	/**
	 * Lidnummer1
	 * Foreign key
	 * @var string
	 */
	public $uid;

	public function getProfiel() {
		return ProfielModel::get($this->uid);
	}

	public function getDescription() {
		return LidToestemmingModel::instance()->getDescription($this->module, $this->instelling_id);
	}
}
