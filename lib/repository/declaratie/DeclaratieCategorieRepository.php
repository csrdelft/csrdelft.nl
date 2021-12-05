<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\DeclaratieCategorie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeclaratieCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclaratieCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclaratieCategorie[]    findAll()
 * @method DeclaratieCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieCategorieRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, DeclaratieCategorie::class);
	}

	public function findTuples(): array
	{
		$categories = [];
		foreach ($this->findAll() as $category) {
			$categories[$category->getId()] = $category->getNaam();
		}
		asort($categories);
		return $categories;
	}
}
