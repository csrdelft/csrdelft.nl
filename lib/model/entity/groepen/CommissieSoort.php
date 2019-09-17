<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CommissieSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * (Bestuurs-)Commissie / SjaarCie.
 */
abstract class CommissieSoort extends PersistentEnum {

	/**
	 * Commissie soorten.
	 */
	const Commissie = 'c';
	const SjaarCie = 's';
	const BestuursCommissie = 'b';
	const Extern = 'e';

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
