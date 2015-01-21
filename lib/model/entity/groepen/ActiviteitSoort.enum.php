<?php

/**
 * ActiviteitSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class ActiviteitSoort implements PersistentEnum {

	const Intern = 'i';
	const Extern = 'e';
	const SjaarActie = 's';

	public static function getTypeOptions() {
		return array(self::Intern, self::Extern, self::SjaarActie);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Intern: return 'Interne activiteit';
			case self::Extern: return 'Externe activiteit';
			case self::SjaarsActie: return 'Sjaarsactie';
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Intern:
			case self::Extern:
			case self::SjaarActie:
				return $option;
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

}
