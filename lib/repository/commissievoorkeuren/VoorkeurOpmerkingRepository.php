<?php

namespace CsrDelft\repository\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class VoorkeurOpmerkingRepository
 * @package CsrDelft\repository\commissievoorkeuren
 * @method VoorkeurOpmerking|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoorkeurOpmerking|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoorkeurOpmerking[]    findAll()
 * @method VoorkeurOpmerking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoorkeurOpmerkingRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, VoorkeurOpmerking::class);
	}

	/**
	 * @param Profiel $profiel
	 * @return VoorkeurOpmerking
	 */
	public function getOpmerkingVoorLid(Profiel $profiel) {
		$result = $this->find($profiel->uid);
		if ($result == false) {
			$result = new VoorkeurOpmerking();
			$result->profiel = $profiel;
		}
		return $result;
	}

	/**
	 * Updates the model if it exists, otherwise creates it.
	 * @param PersistentEntity $entity
	 * @return void
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function updateOrCreate(PersistentEntity $entity) {
		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
	}

}
