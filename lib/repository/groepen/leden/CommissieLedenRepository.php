<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\CommissieLid;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class CommissieLedenRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, CommissieLid::class);
	}
}
