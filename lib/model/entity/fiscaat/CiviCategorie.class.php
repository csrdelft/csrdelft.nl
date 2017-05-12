<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

require_once 'model/entity/fiscaat/CiviSaldoCommissieEnum.class.php';

/**
 * Class CiviCategorie
 *
 * Een Product kan onderdeel van een categorie zijn. Deze categorie hoort ook bij een commissie.
 *
 * Als er veel gebruik gemaakt gaat worden van categorien en commissies moet hier uitgebreid worden.
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
		'cie' => array(T::Enumeration, false, CiviSaldoCommissieEnum::class)
	);
	protected static $primary_key = array('id');
}
