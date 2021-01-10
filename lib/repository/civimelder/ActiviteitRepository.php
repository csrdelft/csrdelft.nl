<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Activiteit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activiteit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activiteit[]    findAll()
 * @method Activiteit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteitRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Activiteit::class);
	}

	/**
	 * @param Reeks $reeks
	 * @return Collection|Activiteit[]
	 */
	public function getKomendeActiviteiten(Reeks $reeks) {
		return $reeks->getActiviteiten()->filter(function(Activiteit $activiteit){
			return $activiteit->magBekijken() && $activiteit->isInToekomst();
		});
	}
}
