<?php

/**
 * ActiviteitSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Algemene en specifieke activiteitsoorten.
 * 
 */
abstract class ActiviteitSoort implements PersistentEnum {

	const Intern = 'i';
	const Extern = 'e';
	const SjaarActie = 's';
	const OWee = 'o';
	const Dies = 'd';
	const Lustrum = 'l';
	const IFES = 'f';

	public static function getTypeOptions() {
		return array(self::Intern, self::Extern, self::SjaarActie, self::OWee, self::Dies, self::Lustrum, self::IFES);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Intern: return 'Interne activiteit';
			case self::Extern: return 'Externe activiteit';
			case self::SjaarsActie: return 'Sjaarsactie';
			case self::OWee: return 'OWee-activiteit';
			case self::Dies: return 'Dies-activiteit';
			case self::Lustrum: return 'Lustrum-activiteit';
			case self::IFES: return 'Activiteit van IFES';
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Intern:
			case self::Extern:
			case self::SjaarActie:
			case self::OWee:
			case self::Dies:
			case self::Lustrum:
			case self::IFES:
				return $option;
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

}
