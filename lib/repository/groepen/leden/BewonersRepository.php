<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\WoonoordBewoner;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */
class BewonersRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, WoonoordBewoner::class);
	}
}
