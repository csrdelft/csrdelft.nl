<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Declaratie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Declaratie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Declaratie[]    findAll()
 * @method Declaratie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Declaratie::class);
	}

	public function verwijderen(Declaratie $declaratie) {
		foreach ($declaratie->getBonnen() as $bon) {
			foreach ($bon->getRegels() as $regel) {
				$this->remove($regel);
			}
			$this->getEntityManager()->flush();
			$this->remove($bon);
		}
		$this->getEntityManager()->flush();
	}
}
