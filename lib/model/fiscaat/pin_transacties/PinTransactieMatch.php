<?php

namespace CsrDelft\model\fiscaat\pin_transacties;


use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\fiscaat\PinTransactie;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 * @since 20/02/2018
 */
class PinTransactieMatch
{
	/**
	 * Reason constants.
	 */
	const REASON_MATCH = 'match';
	const REASON_TRANSPOSE = 'transpose';
	const REASON_VERKEERD_BEDRAG = 'verkeerd bedrag';
	const REASON_MISSENDE_TRANSACTIE = 'missende transactie';
	const REASON_MISSENDE_BESTELLING = 'missende bestelling';

	public $reason;
	public $pinTransactie;
	public $bestelling;

	/**
	 * PinTransactieMismatch constructor.
	 * @param string $reason
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $bestelling
	 * @throws CsrException
	 */
	public function __construct($reason, $pinTransactie, $bestelling)
	{
		$this->assertValidReason($reason);

		$this->reason = $reason;
		$this->pinTransactie = $pinTransactie;
		$this->bestelling = $bestelling;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $bestelling
	 * @return static
	 * @throws CsrException
	 */
	public static function omgedraaid($pinTransactie, $bestelling)
	{
		return new static(self::REASON_TRANSPOSE, $pinTransactie, $bestelling);
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $bestelling
	 * @return static
	 * @throws CsrException
	 */
	public static function verkeerdBedrag($pinTransactie, $bestelling)
	{
		return new static(self::REASON_VERKEERD_BEDRAG, $pinTransactie, $bestelling);
	}

	/**
	 * @param CiviBestellingInhoud $bestelling
	 * @return static
	 * @throws CsrException
	 */
	public static function missendeTransactie($bestelling)
	{
		return new static(self::REASON_MISSENDE_TRANSACTIE, null, $bestelling);
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return static
	 * @throws CsrException
	 */
	public static function missendeBestelling($pinTransactie)
	{
		return new static(self::REASON_MISSENDE_BESTELLING, $pinTransactie, null);
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $bestelling
	 * @return static
	 * @throws CsrException
	 */
	public static function match($pinTransactie, $bestelling)
	{
		return new static(self::REASON_MATCH, $pinTransactie, $bestelling);
	}

	/**
	 * @param string $reason
	 * @throws CsrException
	 */
	private function assertValidReason($reason)
	{
		if (!in_array($reason, [self::REASON_TRANSPOSE, self::REASON_VERKEERD_BEDRAG, self::REASON_MISSENDE_TRANSACTIE, self::REASON_MISSENDE_BESTELLING, self::REASON_MATCH])) {
			throw new CsrException(sprintf('Ongeldige reden %s', $reason));
		}
	}
}
