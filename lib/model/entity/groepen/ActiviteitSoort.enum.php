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

	const Vereniging = 'i'; // intern
	const Verticale = 'v';
	const Lichting = 'l';
	const SjaarsActie = 's';
	const Dies = 'd';
	const Lustrum = 'dl'; // dies lustrum
	const OWee = 'o';
	const IFES = 'f';
	const Extern = 'e';

	public static function getTypeOptions() {
		return array(self::Vereniging, self::Verticale, self::Lichting, self::SjaarsActie, self::Dies, self::Lustrum, self::OWee, self::IFES, self::Extern);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Vereniging: return 'Verenigings-activiteit';
			case self::Verticale: return 'Verticale-activiteit';
			case self::Lichting: return 'Lichtings-activiteit';
			case self::SjaarsActie: return 'Sjaarsactie';
			case self::Dies: return 'Dies-activiteit';
			case self::Lustrum: return 'Lustrum-activiteit';
			case self::OWee: return 'OWee-activiteit';
			case self::IFES: return 'Activiteit van IFES';
			case self::Extern: return 'Externe activiteit';
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Vereniging:
			case self::Verticale:
			case self::Lichting:
			case self::SjaarsActie:
			case self::Dies:
			case self::Lustrum:
			case self::OWee:
			case self::IFES:
			case self::Extern:
				return ucfirst($option);
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

}
