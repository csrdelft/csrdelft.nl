<?php

namespace CsrDelft\repository;

use CsrDelft\entity\turf\TurfGroep;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class TurfGroepRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, TurfGroep::class);
	}

	/**
	 * @param string $groepnaam
	 */
	public function nieuw($groepnaam): TurfGroep
	{
		$groep = new TurfGroep();
		$groep->naam = $groepnaam;
		return $groep;
	}
}
