<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\ActiviteitDeelnemer;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class ActiviteitDeelnemersModel extends KetzerDeelnemersModel {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ActiviteitDeelnemer::class);
	}

	const ORM = ActiviteitDeelnemer::class;
}
