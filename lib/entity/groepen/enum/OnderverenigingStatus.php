<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * OnderverenigingStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een ondervereniging.
 *
 * @method static static AdspirantOndervereniging
 * @method static static Ondervereniging
 * @method static static VoormaligOndervereniging
 */
class OnderverenigingStatus extends Enum
{
	/**
	 * OnderverenigingStatus opties.
	 */
	const AdspirantOndervereniging = 'a';
	const Ondervereniging = 'o';
	const VoormaligOndervereniging = 'v';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::AdspirantOndervereniging => 'Adspirant-ondervereniging',
		self::Ondervereniging => 'Ondervereniging',
		self::VoormaligOndervereniging => 'Voormalig Ondervereniging',
	];
}
