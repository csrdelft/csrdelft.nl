<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

require_once 'peilingen/model/entity/Peiling.class.php';
require_once 'peilingen/model/entity/PeilingOptie.class.php';
require_once 'peilingen/model/entity/PeilingStem.class.php';

/**
 * PeilingenModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * 
 * Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
 * 
 */
class PeilingenModel extends PersistenceModel {

	const ORM = Peiling::class;
	const DIR = 'peilingen/';

	protected static $instance;

	/**
	 * @param PersistentEntity|Peiling $entity
	 * @return int
	 */
	public function update(PersistentEntity $entity) {
		foreach ($entity->getOpties() as $optie) {
			PeilingOptiesModel::instance()->update($optie);
		}

		return parent::update($entity);
	}

	/**
	 * @param PersistentEntity|Peiling $entity
	 * @return int
	 */
	public function delete(PersistentEntity $entity) {
		foreach ($entity->getOpties() as $optie) {
			PeilingOptiesModel::instance()->delete($optie);
		}

		$stemmen = PeilingStemmenModel::instance()->find('peiling_id = ?', array($entity->id))->fetchAll();
		foreach ($stemmen as $stem) {
			echo PeilingStemmenModel::instance()->delete($stem);
		}

		return parent::delete($entity);
	}

	/**
	 * @param PersistentEntity|Peiling $entity
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		$peiling_id = parent::create($entity);

		foreach ($entity->getOpties() as $optie) {
			$optie->peiling_id = $peiling_id;
			PeilingOptiesModel::instance()->create($optie);
		}

		return $peiling_id;
	}

	public function stem($peiling_id, $optieid) {
		$peiling = $this->getPeilingById((int) $peiling_id);
		if ($peiling->magStemmen()) {
			$optie = PeilingOptiesModel::instance()->find('peiling_id = ? AND id = ?', array($peiling_id, $optieid))->fetch();
			$optie->stemmen += 1;

			$stem = new PeilingStem();
			$stem->peiling_id = $peiling->id;
			$stem->uid = LoginModel::getUid();

			try {
				PeilingStemmenModel::instance()->create($stem);
				PeilingOptiesModel::instance()->update($optie);
			} catch (Exception $e) {
				setMelding($e->getMessage(), -1);
			}
		} else {
			setMelding("Stemmen niet toegestaan", -1);
		}
	}

	public function validate(Peiling $entity) {
		$errors = '';
		if ($entity == null) {
			throw new Exception('Peiling is leeg');
		}
		if (trim($entity->tekst) == '') {
			$errors .= 'Tekst mag niet leeg zijn.<br />';
		}
		if (trim($entity->titel) == '') {
			$errors .= 'Titel mag niet leeg zijn.<br />';
		}
		if (count($entity->getOpties()) == 0) {
			$errors .= 'Er moet tenminste 1 optie zijn.<br />';
		}
		return $errors;
	}

	/**
	 * @param $peiling_id
	 * @return Peiling|false
	 */
	public function getPeilingById($peiling_id) {
		return $this->retrieveByPrimaryKey(array($peiling_id));
	}

	public function getLijst() {
		return $this->find(null, array(), null, 'id DESC');
	}

}

class PeilingOptiesModel extends PersistenceModel {

	const ORM = PeilingOptie::class;
	const DIR = 'peilingen/';

	protected static $instance;

}

class PeilingStemmenModel extends PersistenceModel {

	const ORM = PeilingStem::class;
	const DIR = 'peilingen/';

	protected static $instance;

	public function heeftGestemd($peiling_id, $uid) {
		return $this->existsByPrimaryKey(array($peiling_id, $uid));
	}

}
