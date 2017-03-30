<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * ActiviteitSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Algemene en specifieke activiteitsoorten.
 * 
 */
abstract class ActiviteitSoort implements PersistentEnum {

	const VERENIGING = 'vereniging';
	const LUSTRUM = 'lustrum';
	const DIES = 'dies';
	const OWEE = 'owee';
	const SJAARSACTIE = 'sjaarsactie';
	const LICHTING = 'lichting';
	const VERTICALE = 'verticale';
	const KRING = 'kring';
	const HUIS = 'huis';
	const ONDERVERENIGING = 'ondervereniging';
	const IFES = 'ifes';
	const EXTERN = 'extern';

	public static function getTypeOptions() {
		return array(self::VERENIGING, self::LUSTRUM, self::DIES, self::OWEE, self::SJAARSACTIE, self::LICHTING, self::VERTICALE, self::KRING, self::HUIS, self::ONDERVERENIGING, self::IFES, self::EXTERN);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::VERENIGING: return 'Verenigings-activiteit';
			case self::LUSTRUM: return 'Lustrum-activiteit';
			case self::DIES: return 'Dies-activiteit';
			case self::OWEE: return 'OWee-activiteit';
			case self::SJAARSACTIE: return 'Sjaarsactie';
			case self::LICHTING: return 'Lichtings-activiteit';
			case self::VERTICALE: return 'Verticale-activiteit';
			case self::KRING: return 'Kring-activiteit';
			case self::HUIS: return 'Huis-activiteit';
			case self::ONDERVERENIGING: return 'Onderverenigings-activiteit';
			case self::IFES: return 'Activiteit van IFES';
			case self::EXTERN: return 'Externe activiteit';
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::VERENIGING:
			case self::LUSTRUM:
			case self::DIES:
			case self::OWEE:
			case self::SJAARSACTIE:
			case self::LICHTING:
			case self::VERTICALE:
			case self::KRING:
			case self::HUIS:
			case self::ONDERVERENIGING:
			case self::IFES:
			case self::EXTERN:
				return strtoupper(substr($option, 0, 2));
			default: throw new Exception('ActiviteitSoort onbekend');
		}
	}

}
