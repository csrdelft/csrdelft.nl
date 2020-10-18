<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\DeclaratieBon;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeclaratieBon|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclaratieBon|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclaratieBon[]    findAll()
 * @method DeclaratieBon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieBonRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, DeclaratieBon::class);
	}
}
