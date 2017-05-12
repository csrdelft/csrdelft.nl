<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CiviSaldoCommissieEnum.class.php
 *
 * Maak onderscheid tussen verschillende commissies die uit hetzelfde potje geld halen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoCommissieEnum implements PersistentEnum {

	const MAALCIE = 'maalcie';
	const SOCCIE = 'soccie';
	const ANDERS = 'anders';

	public static function getTypeOptions() {
		return array(self::ANDERS, self::SOCCIE, self::MAALCIE);
	}

	public static function getDescription($option) {
		return sprintf('Commissie: %s', $option);
	}

	public static function getChar($option) {
		return ucfirst($option);
	}
}
