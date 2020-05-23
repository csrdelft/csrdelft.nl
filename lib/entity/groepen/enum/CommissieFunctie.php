<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * CommissieFunctie.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Standaard functies binnen het bestuur en commissies.
 *
 * @method static static Praeses
 * @method static static Abactis
 * @method static static Fiscus
 * @method static static VicePraeses
 * @method static static ViceAbactis
 * @method static static QQ
 * @method static static Bibliothecarus
 * @method static static Archivarus
 * @method static static Statisticus
 * @method static static Fotocommissaris
*/
class CommissieFunctie extends Enum {

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
