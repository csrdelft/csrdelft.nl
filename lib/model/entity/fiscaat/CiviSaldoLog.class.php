<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class CiviSaldoLog extends PersistentEntity {
	public $id;
	public $ip;
	public $type;
	public $data;
	public $timestamp;

	protected static $table_name = 'CiviLog';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'ip' => array(T::String),
		'type' => array(T::Enumeration, false, CiviSaldoLogEnum::class),
		'data' => array(T::Text),
		'timestamp' => array(T::Timestamp)
	);
	protected static $primary_key = array('id');
}
