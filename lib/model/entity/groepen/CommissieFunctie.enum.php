<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CommissieFunctie.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Standaard functies binnen het bestuur en commissies.
 * 
 */
abstract class CommissieFunctie implements PersistentEnum {

	// Bestuur
	const PRAESES = 'Praeses';
	const ABACTIS = 'Abactis';
	const FISCUS = 'Fiscus';
	const VICE_PRAESES = 'Vice-Praeses';
	const VICE_ABACTIS = 'Vice-Abactis';
	// Commissie
	const QQ = 'Q.Q.';
	// BASFCie
	const BIBLIOTHECARUS = 'Bibliothecarus';
	const ARCHIVARUS = 'Archivarus';
	const STATISTICUS = 'Statisticus';
	const FOTOCOMMISSARIS = 'Fotocommissaris';

	public static function getTypeOptions() {
		return array(self::PRAESES, self::ABACTIS, self::FISCUS, self::VICE_PRAESES, self::VICE_ABACTIS, self::QQ, self::BIBLIOTHECARUS, self::ARCHIVARUS, self::STATISTICUS, self::FOTOCOMMISSARIS);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::PRAESES:
			case self::ABACTIS:
			case self::FISCUS:
			case self::VICE_PRAESES:
			case self::VICE_ABACTIS:
			case self::QQ:
			case self::BIBLIOTHECARUS:
			case self::ARCHIVARUS:
			case self::STATISTICUS:
			case self::FOTOCOMMISSARIS:
				return $option;
			default: throw new Exception('CommissieFunctie onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::PRAESES:
			case self::ABACTIS:
			case self::FISCUS:
			case self::VICE_PRAESES:
			case self::VICE_ABACTIS:
			case self::QQ:
			case self::BIBLIOTHECARUS:
			case self::ARCHIVARUS:
			case self::STATISTICUS:
			case self::FOTOCOMMISSARIS:
				return '';
			default: throw new Exception('CommissieFunctie onbekend');
		}
	}

}
