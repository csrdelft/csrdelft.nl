<?php

namespace CsrDelft\repository\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\model\OrmTrait;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * @method EetplanBekenden|null find($id, $lockMode = null, $lockVersion = null)
 * @method EetplanBekenden|null findOneBy(array $criteria, array $orderBy = null)
 * @method EetplanBekenden[]    findAll()
 * @method EetplanBekenden[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method EetplanBekenden|null retrieveByUuid($UUID)
 */
class EetplanBekendenRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, EetplanBekenden::class);
	}

	/**
	 * @param string $lichting
	 *
	 * @return EetplanBekenden[]
	 */
	public function getBekenden($lichting) {
		return $this->createQueryBuilder('b')
			->where('b.uid1 like :lichting')
			->setParameter('lichting', $lichting . '%')
			->getQuery()->getResult();
	}

	/**
	 * @param EetplanBekenden|object $entity
	 *
	 * @return bool
	 */
	public function exists($entity) {
		return $this->find(['uid1' => $entity->uid1, 'uid2' => $entity->uid2]) != null
			|| $this->find(['uid1' => $entity->uid2, 'uid2' => $entity->uid1]) != null;
	}
}
