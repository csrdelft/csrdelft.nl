<?php

namespace CsrDelft\model\entity\fiscaat\pin;

use CsrDelft\Orm\Entity\PersistentEnum;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchStatusEnum extends PersistentEnum {
	/**
	 * PinTransactieMatchStatus opties.
	 */
	const STATUS_MATCH = 'match';
	const STATUS_VERWIJDERD = 'verwijderd';
	const STATUS_VERKEERD_BEDRAG = 'verkeerd bedrag';
	const STATUS_MISSENDE_TRANSACTIE = 'missende transactie';
	const STATUS_MISSENDE_BESTELLING = 'missende bestelling';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::STATUS_MATCH => self::STATUS_MATCH,
		self::STATUS_VERWIJDERD => self::STATUS_VERWIJDERD,
		self::STATUS_VERKEERD_BEDRAG => self::STATUS_VERKEERD_BEDRAG,
		self::STATUS_MISSENDE_TRANSACTIE => self::STATUS_MISSENDE_TRANSACTIE,
		self::STATUS_MISSENDE_BESTELLING => self::STATUS_MISSENDE_BESTELLING,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::STATUS_MATCH => 'Match',
		self::STATUS_VERWIJDERD => 'Verwijderd',
		self::STATUS_VERKEERD_BEDRAG => 'Verkeerd bedrag',
		self::STATUS_MISSENDE_TRANSACTIE => 'Missende transactie',
		self::STATUS_MISSENDE_BESTELLING => 'Missende bestelling',
	];
}
