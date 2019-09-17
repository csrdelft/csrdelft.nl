<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CommissieFunctie.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Standaard functies binnen het bestuur en commissies.
 */
abstract class CommissieFunctie extends PersistentEnum {

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
	protected static $supportedChoices = [
		self::Praeses => self::Praeses,
		self::Abactis => self::Abactis,
		self::Fiscus => self::Fiscus,
		self::VicePraeses => self::VicePraeses,
		self::ViceAbactis => self::ViceAbactis,
		self::QQ => self::QQ,
		self::Bibliothecarus => self::Bibliothecarus,
		self::Archivarus => self::Archivarus,
		self::Statisticus => self::Statisticus,
		self::Fotocommissaris => self::Fotocommissaris,
	];

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
