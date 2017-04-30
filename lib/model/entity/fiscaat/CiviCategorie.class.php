<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

require_once 'model/entity/fiscaat/CiviSaldoCommissieEnum.class.php';

/**
 * Class CiviCategorie
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviCategorie extends PersistentEntity {
	public $id;
	public $type;
	public $status;
	public $cie;

	protected static $table_name = 'CiviCategorie';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'type' => array(T::String),
		'status' => array(T::Integer),
		'cie' => array(T::Enumeration, true, CiviSaldoCommissieEnum::class)
	);
	protected static $primary_key = array('id');
}
