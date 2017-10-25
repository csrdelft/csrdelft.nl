<?php

namespace CsrDelft\model\entity\peilingen;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Class PeilingOptie
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class PeilingOptie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Foreign key
	 * @var int
	 */
	public $peiling_id;
	/**
	 * Titel
	 * @var string
	 */
	public $optie;
	/**
	 * Aantal stemmen
	 * @var int
	 */
	public $stemmen = 0;

	public static function init($optie) {
		$peilingoptie = new PeilingOptie();
		$peilingoptie->optie = $optie;
		return $peilingoptie;
	}

	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'peiling_id' => array(T::Integer),
		'optie' => array(T::String),
		'stemmen' => array(T::Integer)
	);

	protected static $primary_key = array('id');

	protected static $table_name = 'peiling_optie';

}
