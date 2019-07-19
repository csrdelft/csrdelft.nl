<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use JsonSerializable;

/**
 * Saldo.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class Saldo extends PersistentEntity implements JsonSerializable {

	public $cie;
	public $uid;
	public $moment;
	public $saldo;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'cie' => array(T::Enumeration, false, SaldoCommissie::class),
		'uid' => array(T::UID),
		'moment' => array(T::DateTime),
		'saldo' => array(T::Float)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array();
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'saldolog';
}
