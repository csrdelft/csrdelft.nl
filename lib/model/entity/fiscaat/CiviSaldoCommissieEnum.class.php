<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CiviSaldoCommissieEnum.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoCommissieEnum implements PersistentEnum {

	const MAALCIE = 'maalcie';
	const SOCCIE = 'soccie';
	const ANDERS = 'anders';

	public static function getTypeOptions() {
		return array(self::SOCCIE, self::MAALCIE, self::ANDERS);
	}

	public static function getDescription($option) {
		return sprintf('Commissie: %s', $option);
	}

	public static function getChar($option) {
		return ucfirst($option);
	}
}