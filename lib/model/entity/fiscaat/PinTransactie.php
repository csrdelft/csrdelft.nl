<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class PinTransactie
 *
 * @package model\entity\fiscaat
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */
class PinTransactie extends PersistentEntity {
	public $id;
	public $bestelling_id;
	public $datetime;
	public $brand;
	public $merchant;
	public $store;
	public $terminal;
	public $TID;
	public $MID;
	public $ref;
	public $type;
	public $amount;
	public $AUTRSP;
	public $STAN;

	/**
	 * @return string
	 */
	public function getBedragInCenten() {
		list($valuta, $bedrag) = explode(' ', $this->amount);

		if ($valuta !== 'EUR') {
			throw new Exception(vsprintf('Betaling niet in euro id: "%s".', $this->id));
		}

		$centen = ltrim(str_replace(',', '', $bedrag), '0');

		return $centen;
	}

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'bestelling_id' => [T::Integer, true],
		'datetime' => [T::String],
		'brand' => [T::String],
		'merchant' => [T::String],
		'store' => [T::String],
		'terminal' => [T::String],
		'TID' => [T::String],
		'MID' => [T::String],
		'ref' => [T::String],
		'type' => [T::String],
		'amount' => [T::String],
		'AUTRSP' => [T::String],
		'STAN' => [T::String],
	];
	protected static $table_name = 'pin_transacties';
	protected static $primary_key = ['id'];
}
