<?php

namespace CsrDelft\model\entity\fiscaat\pin;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatch extends PersistentEntity {
	public $id;
	public $status;
	public $transactie_id;
	public $bestelling_id;

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $pinBestelling
	 * @return static
	 */
	public static function verkeerdBedrag($pinTransactie, $pinBestelling)
	{
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG;
		$pinTransactieMatch->transactie_id = $pinTransactie->id;
		$pinTransactieMatch->bestelling_id = $pinBestelling->bestelling_id;

		return $pinTransactieMatch;
	}

	/**
	 * @param CiviBestellingInhoud $pinBestelling
	 * @return static
	 */
	public static function missendeTransactie($pinBestelling)
	{
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE;
		$pinTransactieMatch->transactie_id = null;
		$pinTransactieMatch->bestelling_id = $pinBestelling->bestelling_id;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return static
	 */
	public static function missendeBestelling($pinTransactie)
	{
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING;
		$pinTransactieMatch->transactie_id = $pinTransactie->id;
		$pinTransactieMatch->bestelling_id = null;

		return $pinTransactieMatch;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @param CiviBestellingInhoud $pinBestelling
	 * @return static
	 */
	public static function match($pinTransactie, $pinBestelling)
	{
		$pinTransactieMatch = new static();
		$pinTransactieMatch->status = PinTransactieMatchStatusEnum::STATUS_MATCH;
		$pinTransactieMatch->transactie_id = $pinTransactie->id;
		$pinTransactieMatch->bestelling_id = $pinBestelling->bestelling_id;

		return $pinTransactieMatch;
	}

	protected static $primary_key = ['id'];
	protected static $table_name = 'pin_transactie_match';
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'status' => [T::Enumeration, false, PinTransactieMatchStatusEnum::class],
		'transactie_id' => [T::Integer, true],
		'bestelling_id' => [T::Integer, true],
	];
}
