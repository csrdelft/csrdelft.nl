<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CommissieSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * (Bestuurs-)Commissie / SjaarCie.
 */
class CommissieSoort extends Enum {

	/**
	 * Commissie soorten.
	 */
	const Commissie = 'c';
	const SjaarCie = 's';
	const BestuursCommissie = 'b';
	const Extern = 'e';

	public static function Commissie() {
		return static::from(self::Commissie);
	}

	public static function SjaarCie() {
		return static::from(self::SjaarCie);
	}

	public static function BestuursCommissie() {
		return static::from(self::BestuursCommissie);
	}

	public static function Extern() {
		return static::from(self::Extern);
	}

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Commissie => self::Commissie,
		self::SjaarCie => self::SjaarCie,
		self::BestuursCommissie => self::BestuursCommissie,
		self::Extern => self::Extern,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Commissie => 'Commissie',
		self::SjaarCie => 'SjaarCie',
		self::BestuursCommissie => 'Bestuurscommissie',
		self::Extern => 'Externe commissie',
	];
}
