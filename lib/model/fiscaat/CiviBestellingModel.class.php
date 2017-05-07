<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviBestelling.class.php';
require_once 'model/fiscaat/CiviBestellingInhoudModel.class.php';

class CiviBestellingModel extends PersistenceModel {
	const ORM = CiviBestelling::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	public function getAlleBestellingenVoorLid($uid, $limit = null) {
		return $this->find('uid = ?', [$uid], null, 'moment DESC', $limit);
	}

	public function getBestellingenVoorLid($uid, $limit = null) {
		return $this->find('uid = ? AND deleted = FALSE', array($uid), null, 'moment DESC', $limit);
	}

	/**
	 * @param CiviBestelling[] $bestellingen
	 * @return Generator
	 */
	public function getBeschrijving($bestellingen) {
		foreach ($bestellingen as $bestelling) {
			/** @var CiviBestellingInhoud[] $inhoud */
			$inhoud = $bestelling->getInhoud();
			$bestellingInhoud = [];
			foreach ($inhoud as $item) {
				$bestellingInhoud[] = CiviBestellingInhoudModel::instance()->getBeschrijving($item);
			}

			yield (object) [
				'inhoud' => $bestellingInhoud,
				'moment' => $bestelling->moment,
				'totaal' => $bestelling->totaal
			];
		}
	}

	public function vanMaaltijdAanmelding(MaaltijdAanmelding $aanmelding) {
		$bestelling = new CiviBestelling();
		$bestelling->cie = 'maalcie';
		$bestelling->uid = $aanmelding->uid;
		$bestelling->deleted = false;
		$bestelling->moment = getDateTime();

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = 1 + $aanmelding->aantal_gasten;
		$inhoud->product_id = $aanmelding->getMaaltijd()->product_id;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = CiviProductModel::instance()->getProduct($inhoud->product_id)->prijs * (1 + $aanmelding->aantal_gasten);

		return $bestelling;
	}

	public function vanInleg($bedrag, $uid) {
		$bestelling = new CiviBestelling();
		$bestelling->cie = 'anders';
		$bestelling->uid = $uid;
		$bestelling->deleted = false;
		$bestelling->moment = getDateTime();

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = -$bedrag;
		$inhoud->product_id = 6; // TODO dynamic, is cent

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = CiviProductModel::instance()->getProduct($inhoud->product_id)->prijs * -$bedrag;

		return $bestelling;
	}

	/**
	 * @param PersistentEntity|CiviBestelling $entity
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		$entity->id = parent::create($entity);

		foreach ($entity->inhoud as $bestelling) {
			$bestelling->bestelling_id = $entity->id;
			CiviBestellingInhoudModel::instance()->create($bestelling);
		}

		return $entity->id;
	}
}
