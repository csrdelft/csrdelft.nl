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

	// lezen
	const Bekijken = 'bekijken';
	// schrijven
	const Aanmelden = 'aanmelden';
	const Bewerken = 'bewerken';
	const Afmelden = 'afmelden';
	const Aanmaken = 'aanmaken';
	const Wijzigen = 'wijzigen';
	const Verwijderen = 'verwijderen';
	// beheren
	const Beheren = 'beheren';
	const Rechten = 'rechten';

	public static function getTypeOptions() {
		return array(self::Bekijken, self::Aanmelden, self::Bewerken, self::Afmelden, self::Aanmaken, self::Wijzigen, self::Verwijderen, self::Beheren, self::Rechten);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Bekijken:
			case self::Aanmelden:
			case self::Bewerken:
			case self::Afmelden:
			case self::Aanmaken:
			case self::Wijzigen:
			case self::Verwijderen:
			case self::Beheren:
			case self::Rechten:
				return $option;
			default: throw new Exception('AccessAction onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Bekijken: return 'R'; // retrieve
			case self::Aanmelden: return 'J'; // join
			case self::Bewerken: return 'E'; // edit
			case self::Afmelden: return 'L'; // leave
			case self::Aanmaken: return 'C'; // create
			case self::Wijzigen: return 'U'; // update
			case self::Verwijderen: return 'D'; // delete
			case self::Beheren: return 'M'; // manage
			case self::Rechten: return 'P'; // permissions
			default: throw new Exception('AccessAction onbekend');
		}
	}

}
