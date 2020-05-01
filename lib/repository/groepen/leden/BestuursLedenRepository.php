<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\BestuursLid;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class BestuursLedenRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, BestuursLid::class);
	}
}
