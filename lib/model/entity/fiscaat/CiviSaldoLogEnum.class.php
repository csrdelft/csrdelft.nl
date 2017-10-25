<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CiviSaldoLogEnum.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoLogEnum extends PersistentEnum {

	/** Maken van een bestelling */
	const INSERT_BESTELLING = 'insert';
	/** Verwijderen van een bestelling */
	const REMOVE_BESTELLING = 'remove';

	/** Aanmaken van een Saldo */
	const CREATE_SALDO = 'create';
	/** Veranderen van een saldo */
	const UPDATE_SALDO = 'update';
	/** Verwijderen van een saldo */
	const DELETE_SALDO = 'delete';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::INSERT_BESTELLING => self::INSERT_BESTELLING,
		self::REMOVE_BESTELLING => self::REMOVE_BESTELLING,
		self::CREATE_SALDO => self::CREATE_SALDO,
		self::UPDATE_SALDO => self::UPDATE_SALDO,
		self::DELETE_SALDO => self::DELETE_SALDO,
	];

	/**
	 * @param string $option
	 * @return string
	 */
	public static function getDescription($option) {
		return sprintf('Log van type : %s', $option);
	}
}
