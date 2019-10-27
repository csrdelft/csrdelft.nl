<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatchStatusEnum;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieMatchTableResponse extends DataTableResponse {
	/**
	 * @param PinTransactieMatch|array $entity
	 * @throws \Exception
	 */
	public function renderElement($entity) {
		if ($entity instanceof PinTransactieMatch) {
			if ($entity->bestelling_id !== null) {
				$bestelling = CiviBestellingModel::get($entity->bestelling_id);
				$bestellingBeschrijving = CiviBestellingModel::instance()->getPinBeschrijving($bestelling);
			} else {
				$bestellingBeschrijving = '-';
			}

			if ($entity->transactie_id !== null) {
				$pinTransactie = PinTransactieModel::get($entity->transactie_id);
				$transactieBeschrijving = PinTransactieModel::instance()->getKorteBeschrijving($pinTransactie);
			} else {
				$transactieBeschrijving = '-';
			}

			$moment = PinTransactieMatchModel::instance()->getMoment($entity);

			return [
				'UUID' => $entity->getUUID(),
				'id' => $entity->id,
				'status' => PinTransactieMatchStatusEnum::getDescription($entity->status),
				'moment' => $moment,
				'transactie_id' => $entity->transactie_id,
				'transactie' => $transactieBeschrijving,
				'bestelling_id' => $entity->bestelling_id,
				'bestelling' => $bestellingBeschrijving,
			];
		} else {
			return $entity;
		}
	}
}
