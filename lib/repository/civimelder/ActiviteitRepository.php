<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
	 * Haalt de maaltijden op die beschikbaar zijn voor aanmelding voor het lid in de ingestelde periode vooraf.
	 *
	 * @return Activiteit[]
	 */
	public function getKomendeActiviteiten(Reeks $reeks) {
		/** @var Activiteit[] $activiteiten */
		$activiteiten = $reeks->getActiviteiten()->filter(function(Activiteit $activiteit){
			return $activiteit->magBekijken() && $activiteit->isInToekomst();
		});
		return $activiteiten;
	}
}
