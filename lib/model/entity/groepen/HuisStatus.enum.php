<?php

/**
 * HuisStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een huis / woonoord.
 * 
 */
abstract class HuisStatus implements PersistentEnum {

	const Woonoord = 'w';
	const Huis = 'h';

	public static function getTypeOptions() {
		return array(self::Woonoord, self::Huis);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Woonoord: return 'Woonoord';
			case self::Huis: return 'Huis';
			default: throw new Exception('HuisStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Woonoord: return 'W';
			case self::Huis: return 'H';
			default: throw new Exception('HuisStatus onbekend');
		}
	}

}
