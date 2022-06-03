<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviBestellingInhoud|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviBestellingInhoud|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviBestellingInhoud[]    findAll()
 * @method CiviBestellingInhoud[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviBestellingInhoudRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, CiviBestellingInhoud::class);
	}
}
