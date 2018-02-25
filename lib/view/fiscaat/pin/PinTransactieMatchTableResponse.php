<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;
use CsrDelft\view\formulier\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieMatchTableResponse extends DataTableResponse {
	/**
	 * @param PinTransactieMatch|array $entity
	 * @return string
	 * @throws \CsrDelft\common\CsrException
	 */
	public function getJson($entity) {
		if ($entity instanceof PinTransactieMatch) {

			if ($entity->bestelling_id !== null) {
				$bestellingBeschrijving = CiviBestellingModel::instance()->getPinBeschrijving(CiviBestellingModel::get($entity->bestelling_id));
			} else {
				$bestellingBeschrijving = '';
			}

			if ($entity->transactie_id !== null) {
				$transactieBeschrijving = PinTransactieModel::instance()->getKorteBeschrijving(PinTransactieModel::get($entity->transactie_id));
			} else {
				$transactieBeschrijving = '';
			}

			return parent::getJson([
				'UUID' => $entity->getUUID(),
				'id' => $entity->id,
				'reden' => $entity->reden,
				'moment' => PinTransactieMatchModel::instance()->getMoment($entity),
				'transactie_id' => $entity->transactie_id,
				'transactie' => $transactieBeschrijving,
				'bestelling_id' => $entity->bestelling_id,
				'bestelling' => $bestellingBeschrijving,
			]);
		} else {
			return parent::getJson($entity);
		}
	}
}
