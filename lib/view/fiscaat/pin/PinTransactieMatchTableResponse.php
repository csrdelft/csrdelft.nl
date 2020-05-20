<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\view\datatable\DataTableResponse;
use Exception;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieMatchTableResponse extends DataTableResponse {
	/**
	 * @param PinTransactieMatch|array $entity
	 * @throws Exception
	 */
	public function renderElement($entity) {
		if ($entity instanceof PinTransactieMatch) {
			if ($entity->bestelling !== null) {
				$bestellingBeschrijving = $entity->bestelling->getPinBeschrijving();
			} else {
				$bestellingBeschrijving = '-';
			}

			if ($entity->transactie !== null) {
				$transactieBeschrijving = $entity->transactie->getKorteBeschrijving();
			} else {
				$transactieBeschrijving = '-';
			}

			return [
				'UUID' => $entity->getUUID(),
				'id' => $entity->id,
				'status' => PinTransactieMatchStatusEnum::getDescription($entity->status),
				'moment' => date_format_intl($entity->getMoment(), DATETIME_FORMAT),
				'transactie_id' => $entity->transactie->id ?? null,
				'transactie' => $transactieBeschrijving,
				'bestelling_id' => $entity->bestelling->id ?? null,
				'bestelling' => $bestellingBeschrijving,
			];
		} else {
			return $entity;
		}
	}
}
