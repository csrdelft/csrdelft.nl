<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\entity\declaratie\DeclaratieWachtrij;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeclaratieWachtrij|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclaratieWachtrij|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclaratieWachtrij[]    findAll()
 * @method DeclaratieWachtrij[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieWachtrijRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, DeclaratieWachtrij::class);
	}

	public function mijnWachtrijen(): array
	{
		return array_filter(
			$this->findBy([], ['positie' => 'asc', 'naam' => 'asc']),
			function ($wachtrij): bool {
				return $wachtrij->magBeoordelen();
			}
		);
	}

	/**
	 * @param DeclaratieWachtrij $wachtrij
	 * @return Declaratie[]
	 */
	public function declaratiesInWachtrij(DeclaratieWachtrij $wachtrij): array
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('d')
			->from(Declaratie::class, 'd')
			->leftJoin('d.categorie', 'c')
			->leftJoin('c.wachtrij', 'w')
			->where('w.id = ?1')
			->orderBy('d.id', 'DESC')
			->setParameter(1, $wachtrij->getId());

		return $qb->getQuery()->getResult();
	}

	public function filterDeclaraties(
		DeclaratieWachtrij $wachtrij,
		array $status
	): array {
		return array_filter($this->declaratiesInWachtrij($wachtrij), function (
			$declaratie
		) use ($status): bool {
			return in_array($declaratie->getListStatus(), $status);
		});
	}
}
