<?php
namespace CsrDelft\model\entity;
use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * Geslacht.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class Geslacht extends PersistentEnum {

	const Man = 'm';
	const Vrouw = 'v';

	public static function getTypeOptions() {
		return array(self::Man, self::Vrouw);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Man: return 'man';
			case self::Vrouw: return 'vrouw';
			default: throw new CsrException('Geslacht onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Man: return 'M';
			case self::Vrouw: return 'V';
			default: throw new CsrException('Geslacht onbekend');
		}
	}

}
