<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * GroepTab.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De verschillende tabbladen om een groep weer te geven.
 *
 */
abstract class GroepTab extends PersistentEnum {

	/**
	 * GroepTab opties.
	 */
	const Lijst = 'lijst';
	const Pasfotos = 'pasfotos';
	const Statistiek = 'stats';
	const Emails = 'emails';
	const Eetwens = 'eetwens';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Lijst => self::Lijst,
		self::Pasfotos => self::Pasfotos,
		self::Statistiek => self::Statistiek,
		self::Emails => self::Emails,
		self::Eetwens => self::Eetwens,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Lijst => 'Lijst',
		self::Pasfotos => 'Pasfoto\'s',
		self::Statistiek => 'Statistiek',
		self::Emails => 'E-mails',
		self::Eetwens => 'Allergie/dieet',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Lijst => 'l',
		self::Pasfotos => 'p',
		self::Statistiek => 's',
		self::Emails => 'e',
		self::Eetwens => 'a',
	];
}
