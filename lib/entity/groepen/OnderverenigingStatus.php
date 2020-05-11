<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * OnderverenigingStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een ondervereniging.
 */
class OnderverenigingStatus extends Enum {

	/**
	 * OnderverenigingStatus opties.
	 */
	const AdspirantOndervereniging = 'a';
	const Ondervereniging = 'o';
	const VoormaligOndervereniging = 'v';

	public static function AdspirantOndervereniging() {
		return static::from(self::AdspirantOndervereniging);
	}

	public static function Ondervereniging() {
		return static::from(self::Ondervereniging);
	}

	public static function VoormaligeOndervereniging() {
		return static::from(self::VoormaligOndervereniging);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::AdspirantOndervereniging => 'Adspirant-ondervereniging',
		self::Ondervereniging => 'Ondervereniging',
		self::VoormaligOndervereniging => 'Voormalig Ondervereniging',
	];
}
