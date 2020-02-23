<?php

namespace CsrDelft\model\fiscaat\pin;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fiscaat\pin\PinTransactie;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class PinTransactieModel
 *
 * @package model\fiscaat
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */
class PinTransactieModel extends PersistenceModel {
	const ORM = PinTransactie::class;

	/**
	 * @param string $from
	 * @param string $to
	 * @return PinTransactie[]
	 */
	public function getPinTransactieInMoment($from, $to) {
		/** @var PinTransactie[] $pinTransacties */
		$pinTransacties = $this->find('datetime > ? AND datetime < ?', [$from, $to], null, 'datetime DESC')->fetchAll();

		return $pinTransacties;
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return string
	 * @throws CsrException
	 */
	public function getKorteBeschrijving($pinTransactie) {
		return sprintf('€%.2f',$pinTransactie->getBedragInCenten()/100);
	}

	/**
	 * @param int $id
	 * @return PinTransactie
	 */
	public function get($id) {
		return $this->find('id = ?', [$id])->fetch();
	}
}
