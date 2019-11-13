<?php

namespace CsrDelft\repository\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\model\OrmTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class EetplanBekendenRepository extends ServiceEntityRepository {
	use OrmTrait {
		exists as ormExists;
	}

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, EetplanBekenden::class);
	}

	/**
	 * @param string $lichting
	 *
	 * @return EetplanBekenden[]
	 */
	public function getBekenden($lichting) {
		return $this->ormFind('uid1 LIKE ?', [$lichting . "%"]);
	}

	/**
	 * @param EetplanBekenden|object $entity
	 *
	 * @return bool
	 */
	public function exists($entity) {
		if ($this->ormExists($entity)) {
			return true;
		}

		$omgekeerd = new EetplanBekenden();
		$omgekeerd->uid1 = $entity->uid2;
		$omgekeerd->uid2 = $entity->uid1;

		return $this->ormExists($omgekeerd);
	}
}
