<?php

namespace CsrDelft\repository\turf;

use CsrDelft\repository\AbstractRepository;
use CsrDelft\entity\turf\TurfGroepLid;
use Doctrine\Persistence\ManagerRegistry;

/** @author Huisman
 */
class TurfGroepLidRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, TurfGroep::class);
	}
}
