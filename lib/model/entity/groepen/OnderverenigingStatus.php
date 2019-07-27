<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * OnderverenigingStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een ondervereniging.
 */
abstract class OnderverenigingStatus extends PersistentEnum {

	/**
	 * OnderverenigingStatus opties.
	 */
	const AdspirantOndervereniging = 'a';
	const Ondervereniging = 'o';
	const VoormaligOndervereniging = 'v';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::AdspirantOndervereniging => self::AdspirantOndervereniging,
		self::Ondervereniging => self::Ondervereniging,
		self::VoormaligOndervereniging => self::VoormaligOndervereniging,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::AdspirantOndervereniging => 'Adspirant-ondervereniging',
		self::Ondervereniging => 'Ondervereniging',
		self::VoormaligOndervereniging => 'Voormalig Ondervereniging',
	];
}
