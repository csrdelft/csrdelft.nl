<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * HuisStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een huis / woonoord.
 * 
 */
abstract class HuisStatus implements PersistentEnum {

	const WOONOORD = 'w';
	const HUIS = 'h';

	public static function getTypeOptions() {
		return array(self::WOONOORD, self::HUIS);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::WOONOORD: return 'Woonoord';
			case self::HUIS: return 'Huis';
			default: throw new Exception('HuisStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::WOONOORD: return 'W';
			case self::HUIS: return 'H';
			default: throw new Exception('HuisStatus onbekend');
		}
	}

}
