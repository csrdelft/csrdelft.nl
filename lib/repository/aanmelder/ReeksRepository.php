<?php

namespace CsrDelft\repository\aanmelder;

use CsrDelft\common\CsrException;
use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reeks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reeks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reeks[]    findAll()
 * @method Reeks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Reeks|null retrieveByUuid($UUID)
 */
class ReeksRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Reeks::class);
	}


	public function delete(Reeks $reeks)
	{
		$em = $this->getEntityManager();

		$em->beginTransaction();
		try {
			foreach ($reeks->getActiviteiten() as $activiteit) {
				$em->remove($activiteit);
			}

			$em->remove($reeks);
			$em->flush();
			$em->commit();
		} catch (ORMException $ex) {
			$em->rollback();
			throw new CsrException($ex->getMessage());
		}
	}
}
