<?php

namespace CsrDelft\repository\forum;

use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @method ForumCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumCategorieRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $managerRegistry)
	{
		parent::__construct($managerRegistry, ForumCategorie::class);
	}

	public function findAll(): array
	{
		return $this->findBy([], ['volgorde' => 'ASC']);
	}
}
