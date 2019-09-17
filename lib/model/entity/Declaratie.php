<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class Declaratie extends PersistentEntity {
	public $id;
	public $commissie;
	public $naam;
	public $email;
	public $datum;
	public $datum_invullen;
	public $iban;
	public $opmerkingen;

	/**
	 * @var DeclaratieRegel[]
	 */
	public $declaratie_regels = [];



	protected static $table_name = 'declaratie';
	protected static $primary_key = ['id'];

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'commissie' => [T::String],
		'naam' => [T::String],
		'email' => [T::String],
		'datum' => [T::Date],
		'datum_invullen' => [T::Date],
		'iban' => [T::String],
		'opmerkingen' => [T::Text],
	];
}
