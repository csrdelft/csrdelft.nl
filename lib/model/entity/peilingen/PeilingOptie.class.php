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
	public $titel;

	public $beschrijving;
	/**
	 * Aantal stemmen
	 * @var int
	 */
	public $stemmen = 0;

	public $ingebracht_door;

	public static function init($optie) {
		$peilingoptie = new PeilingOptie();
		$peilingoptie->optie = $optie;
		return $peilingoptie;
	}

	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'peiling_id' => array(T::Integer),
		'titel' => array(T::String),
		'beschrijving' => array(T::Text, true),
		'stemmen' => array(T::Integer),
		'ingebracht_door' => array(T::UID, true),
	);

	protected static $primary_key = array('id');

	protected static $table_name = 'peiling_optie';

}
