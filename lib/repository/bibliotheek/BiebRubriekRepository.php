<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\entity\bibliotheek\BiebRubriek;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BiebRubriek|null find($id, $lockMode = null, $lockVersion = null)
 * @method BiebRubriek|null findOneBy(array $criteria, array $orderBy = null)
 * @method BiebRubriek[]    findAll()
 * @method BiebRubriek[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BiebRubriekRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, BiebRubriek::class);
	}
}
