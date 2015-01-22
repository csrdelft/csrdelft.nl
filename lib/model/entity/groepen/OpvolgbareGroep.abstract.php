<?php

require_once 'model/entity/groepen/Groep.class.php';

/**
 * OpvolgbareGroep.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep met familie voor opvolging en status.
 */
abstract class OpvolgbareGroep extends Groep {

	/**
	 * Familie (opvolging)
	 * @var string
	 */
	public $familie_id;
	/**
	 * Jaargang
	 * @var string
	 */
	public $jaargang;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	public $status;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'familie_id' => array(T::String),
		'jaargang'	 => array(T::String),
		'status'	 => array(T::Enumeration, false, 'GroepStatus')
	);

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

}
