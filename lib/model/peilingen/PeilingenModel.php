<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\entity\peilingen\PeilingStem;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use PDOStatement;

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
	 * @var PeilingOptiesModel
	 */
	private $peilingOptiesModel;
	/**
	 * @var PeilingStemmenModel
	 */
	private $peilingStemmenModel;

	public function __construct(PeilingOptiesModel $peilingOptiesModel, PeilingStemmenModel $peilingStemmenModel) {
		parent::__construct();

		$this->peilingOptiesModel = $peilingOptiesModel;
		$this->peilingStemmenModel = $peilingStemmenModel;
	}

	/**
	 * @param PersistentEntity|Peiling $entity
	 * @return int
	 */
	public function update(PersistentEntity $entity) {
		foreach ($entity->opties as $optie) {
			$this->peilingOptiesModel->update($optie);
		}

		return parent::update($entity);
	}

	/**
	 * @param PersistentEntity|Peiling $entity
	 * @return int
	 */
	public function delete(PersistentEntity $entity) {
		foreach ($entity->opties as $optie) {
			$this->peilingOptiesModel->delete($optie);
		}

		$stemmen = $this->peilingStemmenModel->find('peiling_id = ?', array($entity->id))->fetchAll();
		foreach ($stemmen as $stem) {
			echo $this->peilingStemmenModel->delete($stem);
		}

		return parent::delete($entity);
	}

	/**
	 * @param PersistentEntity|Peiling $entity
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		$peiling_id = parent::create($entity);

		foreach ($entity->opties as $optie) {
			$optie->peiling_id = $peiling_id;
			$this->peilingOptiesModel->create($optie);
		}

		return $peiling_id;
	}

	/**
	 * @param int $peiling_id
	 * @param int $optie_id
	 */
	public function stem($peiling_id, $optie_id) {
		$peiling = $this->getPeilingById((int)$peiling_id);
		if ($peiling->getMagStemmen() && !$peiling->getHeeftGestemd()) {
			$optie = $this->peilingOptiesModel->find('peiling_id = ? AND id = ?', array($peiling_id, $optie_id))->fetch();
			$optie->stemmen += 1;

			$stem = new PeilingStem();
			$stem->peiling_id = $peiling->id;
			$stem->uid = LoginModel::getUid();

			try {
				$this->peilingStemmenModel->create($stem);
				$this->peilingOptiesModel->update($optie);
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
		if (count($entity->opties) == 0) {
			$errors .= 'Er moet tenminste 1 optie zijn.<br />';
		}
		return $errors;
	}

	public function getPeilingenVoorBeheer() {

		$peilingen = $this->find();
		if (LoginModel::mag(P_PEILING_MOD)) {
			return $peilingen->fetchAll();
		} else {
			$zichtbarePeilingen = $this->find('eigenaar = ?', [LoginModel::getUid()])->fetchAll();
			$peilingenMetRechten = $this->find('eigenaar <> ? AND rechten_mod <> ""', [LoginModel::getUid()]);
			foreach ($peilingenMetRechten as $peiling) {
				if (LoginModel::mag($peiling->rechten_mod)) {
					$zichtbarePeilingen[] = $peiling;
				}
			}

			return $zichtbarePeilingen;
		}
	}

	/**
	 * @param $peiling_id
	 * @return Peiling|false
	 */
	public function getPeilingById($peiling_id) {
		return $this->retrieveByPrimaryKey(array($peiling_id));
	}

	public function magBewerken($peiling) {
		if (LoginModel::mag(P_PEILING_MOD)
			|| $peiling->eigenaar == LoginModel::getUid()
			|| LoginModel::mag($peiling->rechten_mod)) {
			return $peiling;
		}

		return false;
	}

	/**
	 * @return PDOStatement|Peiling[]
	 */
	public function getLijst() {
		return $this->find(null, array(), null, 'id DESC');
	}
}
