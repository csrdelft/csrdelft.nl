<?php

/**
 * GroepTab.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De verschillende tabbladen om een groep weer te geven.
 * 
 */
abstract class GroepTab implements PersistentEnum {

	const Lijst = 'lijst';
	const Pasfotos = 'pasfotos';
	const Statistiek = 'stats';
	const Emails = 'emails';

	public static function getTypeOptions() {
		return array(self::Lijst, self::Pasfotos, self::Statistiek, self::Emails);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Lijst: return 'Lijst';
			case self::Pasfotos: return 'Pasfoto\'s';
			case self::Statistiek: return 'Statistiek';
			case self::Emails: return 'E-mails';
			default: throw new Exception('GroepTab onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Lijst: return 'l';
			case self::Pasfotos: return 'p';
			case self::Statistiek: return 's';
			case self::Emails: return 'e';
			default: throw new Exception('GroepTab onbekend');
		}
	}

}
