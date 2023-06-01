<?php

namespace CsrDelft\repository\turf;

use CsrDelft\repository\AbstractRepository;
use CsrDelft\entity\turf\TurfGroep;
use Doctrine\Persistence\ManagerRegistry;

/** @author Huisman
 */
class TurfGroepRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, TurfGroep::class);
	}

	/**
	 * @param string $groepnaam Naam van groep
	 * @param bool $openbaar Durf je je turfjes de hele Civitas te laten zien?
	 */
	public function nieuw($groepnaam, $openbaar): TurfGroep
	{
		$groep = new TurfGroep();
		$groep->naam = $groepnaam;
		$groep->openbaar = $openbaar;
		return $groep;
	}
}
