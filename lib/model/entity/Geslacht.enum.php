<?php

/**
 * Geslacht.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class Geslacht implements PersistentEnum {

	const Man = 'm';
	const Vrouw = 'v';

	public static function getTypeOptions() {
		return array(self::Man, self::Vrouw);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Man: return 'man';
			case self::Vrouw: return 'vrouw';
			default: throw new Exception('Geslacht onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Man: return 'M';
			case self::Vrouw: return 'V';
			default: throw new Exception('Geslacht onbekend');
		}
	}

}
