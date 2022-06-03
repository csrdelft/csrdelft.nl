<?php

namespace CsrDelft\repository\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class VoorkeurCommissieCategorieRepository
 * @package CsrDelft\repository\commissievoorkeuren
 * @method VoorkeurCommissieCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoorkeurCommissieCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoorkeurCommissieCategorie[]    findAll()
 * @method VoorkeurCommissieCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoorkeurCommissieCategorieRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, VoorkeurCommissieCategorie::class);
	}
}
