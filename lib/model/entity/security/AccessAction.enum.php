<?php

/**
 * AccessAction.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * CRUD + groepen-acties.
 * 
 */
abstract class A implements PersistentEnum {

	// lezen
	const Bekijken = 'r'; // retrieve
	// schrijven (groepen)
	const Aanmelden = 'j'; // join
	const Bewerken = 'e'; // edit
	const Afmelden = 'l'; // leave
	const Opvolging = 's'; // sequence
	// schrijven (algemeen)
	const Aanmaken = 'c'; // create
	const Wijzigen = 'u'; // update
	const Verwijderen = 'd'; // delete
	// beheren
	const Beheren = 'm'; // manage
	const Rechten = 'p'; // permissions

	public static function getTypeOptions() {
		return array(self::Bekijken, self::Aanmelden, self::Bewerken, self::Afmelden, self::Opvolging, self::Aanmaken, self::Wijzigen, self::Verwijderen, self::Beheren, self::Rechten);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Bekijken: return 'Bekijken';
			case self::Aanmelden: return 'Aanmelden';
			case self::Bewerken: return 'Aanmelding bewerken';
			case self::Afmelden: return 'Afmelden';
			case self::Opvolging: return 'Opvolging aanpassen';
			case self::Aanmaken: return 'Nieuwe aanmaken';
			case self::Wijzigen: return 'Wijzigen';
			case self::Verwijderen: return 'Verwijderen';
			case self::Beheren: return 'Beheren';
			case self::Rechten: return 'Rechten instellen';
			default: throw new Exception('AccessAction onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Bekijken:
			case self::Aanmelden:
			case self::Bewerken:
			case self::Afmelden:
			case self::Opvolging:
			case self::Aanmaken:
			case self::Wijzigen:
			case self::Verwijderen:
			case self::Beheren:
			case self::Rechten:
				return ucfirst($option);
			default: throw new Exception('AccessAction onbekend');
		}
	}

}
