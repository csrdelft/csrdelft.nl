<?php

namespace CsrDelft\model\entity\agenda;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * AgendaVerbergen.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Items in de agenda kunnen worden verborgen per gebruiker.
 */
class AgendaVerbergen extends PersistentEntity {

	/**
	 * Lidnummer
	 * Shared primary key
	 * @var string
	 */
	public $uid;
	/**
	 * UUID of Agendeerbaar entity
	 * Shared primary key
	 * @var string
	 */
	public $refuuid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid' => array(T::UID),
		'refuuid' => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid', 'refuuid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'agenda_verbergen';

}
