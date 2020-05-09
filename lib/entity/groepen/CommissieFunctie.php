<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * CommissieFunctie.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Standaard functies binnen het bestuur en commissies.
 */
abstract class CommissieFunctie extends Enum {

	/**
	 * Bestuurs functies.
	 */
	const Praeses = 'Praeses';
	const Abactis = 'Abactis';
	const Fiscus = 'Fiscus';
	const VicePraeses = 'Vice-Praeses';
	const ViceAbactis = 'Vice-Abactis';

	/**
	 * Commissie functies.
	 */
	const QQ = 'Q.Q.';

	/**
	 * BASFCie commissie functies.
	 */
	const Bibliothecarus = 'Bibliothecarus';
	const Archivarus = 'Archivarus';
	const Statisticus = 'Statisticus';
	const Fotocommissaris = 'Fotocommissaris';

	public static function Praeses() {
		return static::from(self::Praeses);
	}

	public static function Abactis() {
		return static::from(self::Abactis);
	}

	public static function Fiscus() {
		return static::from(self::Fiscus);
	}

	public static function VicePraeses() {
		return static::from(self::VicePraeses);
	}

	public static function ViceAbactis() {
		return static::from(self::ViceAbactis);
	}

	public static function QQ() {
		return static::from(self::QQ);
	}

	public static function Bibliothecarus() {
		return static::from(self::Bibliothecarus);
	}

	public static function Statisticus() {
		return static::from(self::Statisticus);
	}

	public static function Fotocommissaris() {
		return static::from(self::Fotocommissaris);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Praeses => 'Praeses',
		self::Abactis => 'Abactis',
		self::Fiscus => 'Fiscus',
		self::VicePraeses => 'Vice-Prases',
		self::ViceAbactis => 'Vice-Abactis',
		self::QQ => 'Q.Q.',
		self::Bibliothecarus => 'Bibliothecarus',
		self::Archivarus => 'Archivarus',
		self::Statisticus => 'Statisticus',
		self::Fotocommissaris => 'Fotocommissaris',
	];
}
