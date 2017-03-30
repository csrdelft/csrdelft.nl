<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * Geslacht.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class Geslacht implements PersistentEnum {

	const MAN = 'm';
	const VROUW = 'v';

	public static function getTypeOptions() {
		return array(self::MAN, self::VROUW);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::MAN: return 'man';
			case self::VROUW: return 'vrouw';
			default: throw new Exception('Geslacht onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::MAN: return 'M';
			case self::VROUW: return 'V';
			default: throw new Exception('Geslacht onbekend');
		}
	}

}
