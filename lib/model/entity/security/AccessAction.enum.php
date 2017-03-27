<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * AccessAction.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * CRUD + groepen-acties.
 * 
 */
abstract class AccessAction implements PersistentEnum {

	// lezen
	const BEKIJKEN = 'r'; // retrieve
	// schrijven (groepen)
	const AANMELDEN = 'j'; // join
	const BEWERKEN = 'e'; // edit
	const AFMELDEN = 'l'; // leave
	const OPVOLGING = 's'; // sequence
	// schrijven (algemeen)
	const AANMAKEN = 'c'; // create
	const WIJZIGEN = 'u'; // update
	const VERWIJDEREN = 'd'; // delete
	// beheren
	const BEHEREN = 'm'; // manage
	const RECHTEN = 'p'; // permissions

	public static function getTypeOptions() {
		return array(self::BEKIJKEN, self::AANMELDEN, self::BEWERKEN, self::AFMELDEN, self::OPVOLGING, self::AANMAKEN, self::WIJZIGEN, self::VERWIJDEREN, self::BEHEREN, self::RECHTEN);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::BEKIJKEN: return 'Bekijken';
			case self::AANMELDEN: return 'Aanmelden';
			case self::BEWERKEN: return 'Aanmelding bewerken';
			case self::AFMELDEN: return 'Afmelden';
			case self::OPVOLGING: return 'Opvolging aanpassen';
			case self::AANMAKEN: return 'Nieuwe aanmaken';
			case self::WIJZIGEN: return 'Wijzigen';
			case self::VERWIJDEREN: return 'Verwijderen';
			case self::BEHEREN: return 'Beheren';
			case self::RECHTEN: return 'Rechten instellen';
			default: throw new Exception('AccessAction onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::BEKIJKEN:
			case self::AANMELDEN:
			case self::BEWERKEN:
			case self::AFMELDEN:
			case self::OPVOLGING:
			case self::AANMAKEN:
			case self::WIJZIGEN:
			case self::VERWIJDEREN:
			case self::BEHEREN:
			case self::RECHTEN:
				return ucfirst($option);
			default: throw new Exception('AccessAction onbekend');
		}
	}

}
