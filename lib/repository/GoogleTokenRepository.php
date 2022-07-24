<?php

namespace CsrDelft\repository;

use CsrDelft\entity\GoogleToken;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class GoogleTokenModel.
 *
 * @author G.J.W. Oolbekkink Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @method GoogleToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method GoogleToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method GoogleToken[]    findAll()
 * @method GoogleToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GoogleTokenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, GoogleToken::class);
	}

	/**
	 * @param $uid
	 * @return bool
	 */
	public function exists($uid): bool
	{
		return $this->find($uid) != null;
	}
}
