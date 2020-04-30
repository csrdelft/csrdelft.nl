<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\KetzerDeelnemer;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzerDeelnemersModel extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry, $entityClass = self::ORM) {
		parent::__construct($managerRegistry, $entityClass);
	}
	const ORM = KetzerDeelnemer::class;
}
