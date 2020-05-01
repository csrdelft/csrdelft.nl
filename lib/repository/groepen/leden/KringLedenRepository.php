<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\KringLid;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */
class KringLedenRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, KringLid::class);
	}
}
