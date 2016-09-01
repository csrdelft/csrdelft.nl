<?php

require_once 'model/entity/Peiling.class.php';
require_once 'model/entity/PeilingOptie.class.php';
require_once 'model/entity/PeilingStem.class.php';

/**
 * PeilingenModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * 
 * Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
 * 
 */
class PeilingenModel extends PersistenceModel
{
	const ORM = 'Peiling';
	protected static $instance;

	public function update(PersistentEntity $entity) {
		foreach ($entity->getOpties() as $optie) {
			PeilingOptiesModel::instance()->update($optie);
		}

		return parent::update($entity);
	}

	public function delete(PersistentEntity $entity) {
		foreach ($entity->getOpties() as $optie) {
			PeilingOptiesModel::instance()->delete($optie);
		}

		$stemmen = PeilingStemmenModel::instance()->find('peilingid = ?', array($entity->id))->fetchAll();
		foreach ($stemmen as $stem) {
			echo PeilingStemmenModel::instance()->delete($stem);
		}

		return parent::delete($entity);
	}
	
	public function create(PersistentEntity $entity) {
		$peilingid = parent::create($entity);

		foreach ($entity->getOpties() as $optie) {
			$optie->peilingid = $peilingid;
			PeilingOptiesModel::instance()->create($optie);
		}

		return $peilingid;
	}

	public function stem($peilingid, $optieid) {
	    $peiling = $this->find('id = ?', array($peilingid))->fetch();
        if ($peiling->magStemmen()) {
            $optie = PeilingOptiesModel::instance()->find('peilingid = ? AND id = ?', array($peilingid, $optieid))->fetch();
            $optie->stemmen += 1;

            $stem = new PeilingStem();
            $stem->peilingid = $peiling->id;
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
		if ($entity == null) throw new Exception('Peiling is leeg');

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
	 * @param $peilingid
	 * @return Peiling
	 */
	public function get($peilingid) {
		return $this->retrieveByPrimaryKey(array($peilingid));
	}

	public function lijst() {
		return $this->find(null, array(), null, 'id DESC');
	}
}

class PeilingOptiesModel extends PersistenceModel
{
	const ORM = 'PeilingOptie';

	protected static $instance;
}

class PeilingStemmenModel extends PersistenceModel
{
	const ORM = 'PeilingStem';

	protected static $instance;
}
