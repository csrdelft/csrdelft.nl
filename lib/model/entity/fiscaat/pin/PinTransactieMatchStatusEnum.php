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
	const REASON_MATCH = 'match';
	const REASON_VERKEERD_BEDRAG = 'verkeerd bedrag';
	const REASON_MISSENDE_TRANSACTIE = 'missende transactie';
	const REASON_MISSENDE_BESTELLING = 'missende bestelling';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::REASON_MATCH => self::REASON_MATCH,
		self::REASON_VERKEERD_BEDRAG => self::REASON_VERKEERD_BEDRAG,
		self::REASON_MISSENDE_TRANSACTIE => self::REASON_MISSENDE_TRANSACTIE,
		self::REASON_MISSENDE_BESTELLING => self::REASON_MISSENDE_BESTELLING,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::REASON_MATCH => self::REASON_MATCH,
		self::REASON_VERKEERD_BEDRAG => self::REASON_VERKEERD_BEDRAG,
		self::REASON_MISSENDE_TRANSACTIE => self::REASON_MISSENDE_TRANSACTIE,
		self::REASON_MISSENDE_BESTELLING => self::REASON_MISSENDE_BESTELLING,
	];
}
