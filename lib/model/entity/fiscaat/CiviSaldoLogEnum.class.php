<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CiviSaldoLogEnum.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoLogEnum implements PersistentEnum {

	const INSERT = 'insert';
	const UPDATE = 'update';
	const REMOVE = 'remove';

	public static function getTypeOptions() {
		return array(self::INSERT, self::UPDATE, self::REMOVE);
	}

	public static function getDescription($option) {
		return sprintf('Log van type : %s', $option);
	}

	public static function getChar($option) {
		return ucfirst($option);
	}
}