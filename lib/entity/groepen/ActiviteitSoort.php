<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * ActiviteitSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Algemene en specifieke activiteitsoorten.
 *
 */
class ActiviteitSoort extends Enum {

	/**
	 * ActiviteitSoort opties.
	 */
	const Vereniging = 'vereniging';
	const Lustrum = 'lustrum';
	const Dies = 'dies';
	const OWee = 'owee';
	const SjaarsActie = 'sjaarsactie';
	const Lichting = 'lichting';
	const Verticale = 'verticale';
	const Kring = 'kring';
	const Huis = 'huis';
	const Ondervereniging = 'ondervereniging';
	const IFES = 'ifes';
	const Extern = 'extern';

	public static function Vereniging() {
		return static::from(self::Vereniging);
	}

	public static function Lustrum() {
		return static::from(self::Lustrum);
	}

	public static function Dies() {
		return static::from(self::Dies);
	}

	public static function OWee() {
		return static::from(self::OWee);
	}

	public static function SjaarsActie() {
		return static::from(self::SjaarsActie);
	}

	public static function Lichting() {
		return static::from(self::Lichting);
	}

	public static function Verticale() {
		return static::from(self::Verticale);
	}

	public static function Kring() {
		return static::from(self::Kring);
	}

	public static function Huis() {
		return static::from(self::Huis);
	}

	public static function Ondervereniging() {
		return static::from(self::Ondervereniging);
	}

	public static function IFES() {
		return static::from(self::IFES);
	}

	public static function Extern() {
		return static::from(self::Extern);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Vereniging => 'Verenigings-activiteit',
		self::Lustrum => 'Lustrum-activiteit',
		self::Dies => 'Dies-activiteit',
		self::OWee => 'OWee-activiteit',
		self::SjaarsActie => 'Sjaarsactie',
		self::Lichting => 'Lichtings-activiteit',
		self::Verticale => 'Verticale-activiteit',
		self::Kring => 'Kring-activiteit',
		self::Huis => 'Huis-activiteit',
		self::Ondervereniging => 'Onderverenigings-activiteit',
		self::IFES => 'Activiteit van IFES',
		self::Extern => 'Externe activiteit',
	];
}
