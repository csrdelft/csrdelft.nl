<?php
namespace CsrDelft\model;
use CsrDelft\model\entity\EetplanBekenden;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

/**
 * EetplanBekendenModel.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class EetplanBekendenModel extends PersistenceModel {
	protected static $instance;

	const ORM = EetplanBekenden::class;

	/**
	 * EetplanBekenden constructor.
	 */
	public function __construct() {
		PersistenceModel::__construct();
	}

	public function getBekenden($lichting) {
		return $this->find('uid1 LIKE ?', array($lichting . "%"))->fetchAll();
	}

	/**
	 * @param PersistentEntity|EetplanBekenden $entity
	 * @return bool
	 */
	public function exists(PersistentEntity $entity) {
		if (PersistenceModel::exists($entity)) {
			return true;
		}

		$omgekeerd = new EetplanBekenden();
		$omgekeerd->uid1 = $entity->uid2;
		$omgekeerd->uid2 = $entity->uid1;

		return PersistenceModel::exists($omgekeerd);
	}
}
