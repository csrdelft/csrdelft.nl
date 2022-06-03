<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * ActiviteitSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Algemene en specifieke activiteitsoorten.
 *
 * @method static static Vereniging
 * @method static static Lustrum
 * @method static static Dies
 * @method static static OWee
 * @method static static SjaarsActie
 * @method static static Lichting
 * @method static static Verticale
 * @method static static Kring
 * @method static static Huis
 * @method static static Ondervereniging
 * @method static static IFES
 * @method static static Extern
 */
class ActiviteitSoort extends Enum
{

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
