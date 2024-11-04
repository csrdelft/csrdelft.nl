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


	public function mijnWachtrijen(): array
	{
		return array_filter(
			$this->findBy([], ['positie' => 'asc', 'naam' => 'asc']),
			fn($wachtrij) => $wachtrij->magBeoordelen()
		);
	}

	public function filterDeclaraties(
		DeclaratieWachtrij $wachtrij,
		array $status
	): array {
		return array_filter(
			$this->declaratiesInWachtrij($wachtrij),
			fn($declaratie) => in_array($declaratie->getListStatus(), $status)
		);
	}
}
