<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\Entity\civimelder\Deelnemer;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Deelnemer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deelnemer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deelnemer[]    findAll()
 * @method Deelnemer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeelnemerRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Deelnemer::class);
	}

	public function getAantalAanmeldingen(Activiteit $activiteit): int {
		$q = $this->createQueryBuilder('a')
        ->select('SUM(a.aantal)')
        ->where('a.activiteit = :activiteit')
        ->setParameter('activiteit', $activiteit)
        ->getQuery();

		try {
			return $q->getSingleScalarResult();
		} catch (NoResultException $e) {
			return 0;
		} catch (NonUniqueResultException $e) {
			return 0;
		}
	}

	public function isAangemeld(Activiteit $activiteit, Profiel $profiel) {
		return $this->findOneBy(['activiteit' => $activiteit, 'lid' => $profiel]) !== null;
	}
}
