<?php
use CsrDelft\Orm\Entity\T;

/**
 * CiviSaldo.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldo extends \CsrDelft\Orm\Entity\PersistentEntity {
	public $id;
	public $uid;
	public $naam;
	public $saldo;
	public $laatst_veranderd;

	protected static $persistent_attributes = [
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'naam' => array(T::Text, true),
		'saldo' => array(T::Integer),
		'laatst_veranderd' => array(T::Timestamp),
	];
	protected static $table_name = 'CiviSaldo';
	protected static $primary_key = array('id');
}