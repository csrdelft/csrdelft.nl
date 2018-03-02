<?php

namespace CsrDelft\model\fiscaat\pin;
use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchModel extends PersistenceModel {
	const ORM = PinTransactieMatch::class;

	/**
	 * @param PinTransactieMatch $pinTransactieMatch
	 * @throws CsrException
	 */
	public function getMoment($pinTransactieMatch) {
		if ($pinTransactieMatch->transactie_id !== null) {
			return PinTransactieModel::get($pinTransactieMatch->transactie_id)->datetime;
		} elseif ($pinTransactieMatch->bestelling_id !== null) {
			return CiviBestellingModel::get($pinTransactieMatch->bestelling_id)->moment;
		} else {
			throw new CsrException('Pin Transactie Match heeft geen bestelling en transactie.');
		}
	}
}
