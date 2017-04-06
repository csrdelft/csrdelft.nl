<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviBestelling.class.php';
require_once 'model/fiscaat/CiviBestellingInhoudModel.class.php';

class CiviBestellingModel extends PersistenceModel {
	const ORM = CiviBestelling::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	public function vanMaaltijdAanmelding(MaaltijdAanmelding $aanmelding) {
		$bestelling = new CiviBestelling();
		$bestelling->uid = $aanmelding->uid;
		$bestelling->deleted = false;

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = 1 + $aanmelding->aantal_gasten;
		$inhoud->product_id = $aanmelding->getMaaltijd()->product_id;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = CiviProductModel::instance()->getProduct($inhoud->product_id)->prijs * (1 + $aanmelding->aantal_gasten);

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
