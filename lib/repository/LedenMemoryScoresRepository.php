<?php

namespace CsrDelft\repository;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\LedenMemoryScore;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LedenMemoryScoresModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method LedenMemoryScore|null find($id, $lockMode = null, $lockVersion = null)
 * @method LedenMemoryScore|null findOneBy(array $criteria, array $orderBy = null)
 * @method LedenMemoryScore[]    findAll()
 * @method LedenMemoryScore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LedenMemoryScoresRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, LedenMemoryScore::class);
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'tijd ASC, beurten DESC';

	public function nieuw()
	{
		$score = new LedenMemoryScore();
		$score->door_uid = LoginService::getUid();
		$score->wanneer = DateUtil::getDateTime();
		return $score;
	}

	public function getGroepTopScores(Groep $groep, $limit = 10)
	{
		return $this->findBy(
			['eerlijk' => true, 'groep' => $groep->getUUID()],
			['tijd' => 'ASC', 'beurten' => 'DESC'],
			$limit
		);
	}

	public function create(LedenMemoryScore $ledenMemoryScore)
	{
		$this->getEntityManager()->persist($ledenMemoryScore);
		$this->getEntityManager()->flush();
	}

	public function getAllTopScores()
	{
		return $this->createQueryBuilder('s')
			->where('s.eerlijk = true')
			->groupBy('s.groep')
			->addGroupBy('s.door_uid')
			->having('MIN(s.tijd)')
			->getQuery()
			->getResult();
	}
}
