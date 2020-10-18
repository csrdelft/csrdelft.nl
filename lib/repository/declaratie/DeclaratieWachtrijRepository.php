<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\DeclaratieWachtrij;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeclaratieWachtrij|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclaratieWachtrij|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclaratieWachtrij[]    findAll()
 * @method DeclaratieWachtrij[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieWachtrijRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, DeclaratieWachtrij::class);
	}
}
