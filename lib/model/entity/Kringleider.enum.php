<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * Kringleider.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class Kringleider implements PersistentEnum {

	const OUDEREJAARS = 'o';
	const EERSTEJAARS = 'e';
	const NEE = 'n';

	public static function getTypeOptions() {
		return array(self::OUDEREJAARS, self::EERSTEJAARS, self::NEE);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::OUDEREJAARS: return 'Ouderejaarskring';
			case self::EERSTEJAARS: return 'Eerstejaarskring';
			case self::NEE: return 'Nee';
			default: throw new Exception('Kringleider onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::OUDEREJAARS: return 'O';
			case self::EERSTEJAARS: return 'E';
			case self::NEE: return '-';
			default: throw new Exception('Kringleider onbekend');
		}
	}

}
