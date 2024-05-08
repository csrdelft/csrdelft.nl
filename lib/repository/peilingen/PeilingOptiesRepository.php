<?php

namespace CsrDelft\repository\peilingen;

use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method PeilingOptie|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeilingOptie|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeilingOptie[]    findAll()
 * @method PeilingOptie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method PeilingOptie|null retrieveByUuid($UUID)
 */
class PeilingOptiesRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, PeilingOptie::class);
	}

	/**
	 * Zie PeilingenLogic::getOptiesVoorPeiling
	 *
	 * @param $peilingId
	 * @return PeilingOptie[]
	 */
	public function getByPeilingId($peilingId): array
	{
		return $this->findBy(['peiling_id' => $peilingId]);
	}
}
