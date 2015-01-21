<?php

/**
 * Kringleider.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class Kringleider implements PersistentEnum {

	const Ouderejaars = 'o';
	const Eerstejaars = 'e';
	const Nee = 'n';

	public static function getTypeOptions() {
		return array(self::Ouderejaars, self::Eerstejaars, self::Nee);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Ouderejaars: return 'Ouderejaarskring';
			case self::Eerstejaars: return 'Eerstejaarskring';
			case self::Nee: return 'Nee';
			default: throw new Exception('Kringleider onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Ouderejaars: return 'O';
			case self::Eerstejaars: return 'E';
			case self::Nee: return '-';
			default: throw new Exception('Kringleider onbekend');
		}
	}

}
