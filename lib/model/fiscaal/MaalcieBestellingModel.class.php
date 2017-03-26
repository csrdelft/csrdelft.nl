<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/MaalcieBestelling.class.php';
require_once 'model/fiscaal/MaalcieBestellingInhoudModel.class.php';

class MaalcieBestellingModel extends PersistenceModel {
	const ORM = 'MaalcieBestelling';
	const DIR = 'fiscaal/';

	protected static $instance;

	public function vanMaaltijdAanmelding(MaaltijdAanmelding $aanmelding) {
		$bestelling = new MaalcieBestelling();
		$bestelling->uid = $aanmelding->uid;
		$bestelling->deleted = false;

		$inhoud = new MaalcieBestellingInhoud();
		$inhoud->aantal = 1 + $aanmelding->aantal_gasten;
		$inhoud->productid = $aanmelding->getMaaltijd()->mlt_repetitie_id;

		$bestelling->add($inhoud);

		return $bestelling;
	}

	/**
	 * @param PersistentEntity|MaalcieBestelling $entity
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		$entity->id = parent::create($entity);

		foreach ($entity->inhoud as $bestelling) {
			$bestelling->bestellingid = $entity->id;
			MaalcieBestellingInhoudModel::instance()->create($bestelling);
		}

		return $entity->id;
	}
}
