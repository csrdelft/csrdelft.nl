<?php

namespace CsrDelft\repository\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
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
	 * @param int $lidjaar
	 *
	 * @return EetplanBekenden[]
	 */
	public function getBekendenVoorLidjaar($lidjaar) {
		return $this->createQueryBuilder('b')
			->join('b.noviet1', 'n')
			->where('n.lidjaar = :lidjaar')
			->setParameter('lidjaar', $lidjaar)
			->getQuery()->getResult();
	}

	/**
	 * @param EetplanBekenden|object $entity
	 *
	 * @return bool
	 */
	public function exists($entity) {
		return count($this->findBy(['noviet1' => $entity->noviet1, 'noviet2' => $entity->noviet2])) != 0
			|| count($this->findBy(['noviet1' => $entity->noviet1, 'noviet2' => $entity->noviet1])) != 0;
	}
}
