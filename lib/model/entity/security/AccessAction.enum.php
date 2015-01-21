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

	const Aanmaken = 'create';
	const Bekijken = 'retrieve';
	const Wijzigen = 'update';
	const Verwijderen = 'delete';
	const Beheren = 'manage';
	const Aanmelden = 'join';
	const Afmelden = 'leave';
	const Bewerken = 'edit';

	public static function getTypeOptions() {
		return array(self::Nobody, self::Eter, self::Oudlid, self::Lid, self::Basfcie, self::Maalcie, self::Bestuur, self::Pubcie);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Aanmaken: return 'aanmaken';
			case self::Bekijken: return 'bekijken';
			case self::Wijzigen: return 'wijzigen';
			case self::Verwijderen: return 'verwijderen';
			case self::Beheren: return 'beheren';
			case self::Aanmelden: return 'aanmelden';
			case self::Afmelden: return 'afmelden';
			case self::Bewerken: return 'bewerken';
			default: throw new Exception('Ongeldige AccessAction');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Aanmaken: return 'C';
			case self::Bekijken: return 'R';
			case self::Wijzigen: return 'U';
			case self::Verwijderen: return 'D';
			case self::Beheren: return 'M';
			case self::Aanmelden: return 'J';
			case self::Afmelden: return 'L';
			case self::Bewerken: return 'E';
			default: throw new Exception('Ongeldige AccessAction');
		}
	}

}
