<?php

/**
 * AccessAction.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * CRUD + standaard groep-acties.
 * 
 */
abstract class A implements PersistentEnum {

	const Rechten = 'rechten';
	const Beheren = 'beheren';
	const Aanmaken = 'aanmaken';
	const Bekijken = 'bekijken';
	const Wijzigen = 'wijzigen';
	const Verwijderen = 'verwijderen';
	const Aanmelden = 'aanmelden';
	const Afmelden = 'afmelden';
	const Bewerken = 'bewerken';

	public static function getTypeOptions() {
		return array(self::Rechten, self::Beheren, self::Aanmaken, self::Bekijken, self::Wijzigen, self::Verwijderen, self::Aanmelden, self::Afmelden, self::Bewerken);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Rechten:
			case self::Beheren:
			case self::Aanmaken:
			case self::Bekijken:
			case self::Wijzigen:
			case self::Verwijderen:
			case self::Aanmelden:
			case self::Afmelden:
			case self::Bewerken:
				return $option;
			default: throw new Exception('AccessAction onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Rechten: return 'P';
			case self::Beheren: return 'M';
			case self::Aanmaken: return 'C';
			case self::Bekijken: return 'R';
			case self::Wijzigen: return 'U';
			case self::Verwijderen: return 'D';
			case self::Aanmelden: return 'J';
			case self::Afmelden: return 'L';
			case self::Bewerken: return 'E';
			default: throw new Exception('AccessAction onbekend');
		}
	}

}
