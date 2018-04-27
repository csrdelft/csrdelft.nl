<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\entity\peilingen\PeilingStem;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

/**
 * PeilingenModel.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
 *
 */
class PeilingenModel extends PersistenceModel {

	const ORM = Peiling::class;

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

	/**
	 * @param int $peiling_id
	 * @param int $optie_id
	 */
	public function stem($peiling_id, $optie_id) {
		$peiling = $this->getPeilingById((int)$peiling_id);
		if ($peiling->magStemmen()) {
			$optie = PeilingOptiesModel::instance()->find('peiling_id = ? AND id = ?', array($peiling_id, $optie_id))->fetch();
			$optie->stemmen += 1;

			$stem = new PeilingStem();
			$stem->peiling_id = $peiling->id;
			$stem->uid = LoginModel::getUid();

			try {
				PeilingStemmenModel::instance()->create($stem);
				PeilingOptiesModel::instance()->update($optie);
			} catch (CsrGebruikerException $e) {
				setMelding($e->getMessage(), -1);
			}
		} else {
			setMelding("Stemmen niet toegestaan", -1);
		}
	}

	/**
	 * @param Peiling $entity
	 *
	 * @return string
	 * @throws CsrGebruikerException
	 */
	public function validate(Peiling $entity) {
		$errors = '';
		if ($entity == null) {
			throw new CsrGebruikerException('Peiling is leeg');
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

	/**
	 * @return \PDOStatement|Peiling[]
	 */
	public function getLijst() {
		return $this->find(null, array(), null, 'id DESC');
	}

}
