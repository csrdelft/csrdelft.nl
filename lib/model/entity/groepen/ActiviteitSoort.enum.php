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

	const Vereniging = 'vereniging';
	const Ondervereniging = 'onderv.';
	const SjaarsActie = 'sjaarsactie';
	const Lichting = 'lichting';
	const Verticale = 'verticale';
	const Kring = 'kring';
	const Huis = 'huis';
	const Dies = 'dies';
	const Lustrum = 'lustrum';
	const OWee = 'owee';
	const IFES = 'ifes';
	const Extern = 'extern';

	public static function getTypeOptions() {
		return array(self::Vereniging, self::Ondervereniging, self::SjaarsActie, self::Lichting, self::Verticale, self::Kring, self::Huis, self::Dies, self::Lustrum, self::OWee, self::IFES, self::Extern);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Vereniging: return 'Verenigings-activiteit';
			case self::Ondervereniging: return 'Onderverenigings-activiteit';
			case self::SjaarsActie: return 'Sjaarsactie';
			case self::Lichting: return 'Lichtings-activiteit';
			case self::Verticale: return 'Verticale-activiteit';
			case self::Kring: return 'Kring-activiteit';
			case self::Huis: return 'Huis-activiteit';
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
			case self::Ondervereniging:
			case self::SjaarsActie:
			case self::Lichting:
			case self::Verticale:
			case self::Kring:
			case self::Huis:
			case self::Dies:
			case self::Lustrum:
			case self::OWee:
			case self::IFES:
			case self::Extern:
				return strtoupper(substr($option, 0, 2));
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

}
