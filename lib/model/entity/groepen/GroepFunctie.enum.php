<?php

/**
 * GroepFunctie.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Standaard functies binnen het bestuur en commissies.
 * 
 */
abstract class GroepFunctie implements PersistentEnum {

	// Bestuur
	const Praeses = 'Praeses';
	const Abactis = 'Abactis';
	const Fiscus = 'Fiscus';
	const VicePraeses = 'Vice-Praeses';
	const ViceAbactis = 'Vice-Abactis';
	// Commissie
	const QQ = 'Q.Q.';
	// BAS-FCie
	const Bibliothecarus = 'Bibliothecarus';
	const Archivarus = 'Archivarus';
	const Statisticus = 'Statisticus';
	const Fotocommissaris = 'Fotocommissaris';
	// Werkgroep
	const Leider = 'Leider';

	public static function getTypeOptions() {
		return array(self::Praeses, self::Abactis, self::Fiscus, self::VicePraeses, self::ViceAbactis, self::QQ, self::Bibliothecarus, self::Archivarus, self::Statisticus, self::Fotocommissaris, self::Leider);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Praeses:
			case self::Abactis:
			case self::Fiscus:
			case self::VicePraeses:
			case self::ViceAbactis:
			case self::QQ:
			case self::Bibliothecarus:
			case self::Archivarus:
			case self::Statisticus:
			case self::Fotocommissaris:
			case self::Leider:
				return $option;
			default: throw new Exception('Ongeldige GroepFunctie');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Praeses:
			case self::Abactis:
			case self::Fiscus:
			case self::VicePraeses:
			case self::ViceAbactis:
			case self::QQ:
			case self::Bibliothecarus:
			case self::Archivarus:
			case self::Statisticus:
			case self::Fotocommissaris:
			case self::Leider:
				return '';
			default: throw new Exception('Ongeldige GroepFunctie');
		}
	}

}
