<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\model\fiscaat\CiviBestellingModel;
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
		$container = ContainerFacade::getContainer();
		$pinTransactieRepository = $container->get(PinTransactieRepository::class);
		$pinTransactieMatchRepository = $container->get(PinTransactieMatchRepository::class);
		$civiBestellingModel = $container->get(CiviBestellingModel::class);

		if ($entity instanceof PinTransactieMatch) {
			if ($entity->bestelling_id !== null) {
				$bestelling = $civiBestellingModel->get($entity->bestelling_id);
				$bestellingBeschrijving = $civiBestellingModel->getPinBeschrijving($bestelling);
			} else {
				$bestellingBeschrijving = '-';
			}

			if ($entity->transactie_id !== null) {
				$pinTransactie = $pinTransactieRepository->get($entity->transactie_id);
				$transactieBeschrijving = $pinTransactieRepository->getKorteBeschrijving($pinTransactie);
			} else {
				$transactieBeschrijving = '-';
			}

			$moment = $pinTransactieMatchRepository->getMoment($entity);

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
