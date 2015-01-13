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

	public static function getDescription($status) {
		switch ($status) {
			case self::Ouderejaars: return 'Ouderejaarskring';
			case self::Eerstejaars: return 'Eerstejaarskring';
			case self::Nee: return 'Nee';
			default: throw new Exception('Ongeldige Kringleider');
		}
	}

}
