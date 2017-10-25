<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * ActiviteitSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Algemene en specifieke activiteitsoorten.
 *
 */
abstract class ActiviteitSoort extends PersistentEnum {

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
	protected static $supportedChoices = [
		self::Vereniging => self::Vereniging,
		self::Lustrum => self::Lustrum,
		self::Dies => self::Dies,
		self::OWee => self::OWee,
		self::SjaarsActie => self::SjaarsActie,
		self::Lichting => self::Lichting,
		self::Verticale => self::Verticale,
		self::Kring => self::Kring,
		self::Huis => self::Huis,
		self::Ondervereniging => self::Ondervereniging,
		self::IFES => self::IFES,
		self::Extern => self::Extern,
	];

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

	/**
	 * @param string $option
	 * @return string
	 * @throws CsrException
	 */
	public static function getChar($option) {
		if (isset(static::$supportedChoices[$option])) {
			return strtoupper(substr($option, 0, 2));
		} else {
			throw new CsrException('ActiviteitSoort onbekend');
		}
	}

}
