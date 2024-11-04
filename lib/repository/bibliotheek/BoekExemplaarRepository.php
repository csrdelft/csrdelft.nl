<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\entity\bibliotheek\BoekExemplaar;
use CsrDelft\entity\bibliotheek\BoekExemplaarStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoekExemplaar|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoekExemplaar|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoekExemplaar[]    findAll()
 * @method BoekExemplaar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoekExemplaarRepository extends AbstractRepository
{


	/**
	 * @param $id
	 * @return BoekExemplaar|null
	 */
	public function get($id)
	{
		return $this->find($id);
	}

	/**
	 * @param Profiel $profiel
	 * @return BoekExemplaar[]
	 */
	public function getGeleend(Profiel $profiel)
	{
		return $this->findBy(['uitgeleend_uid' => $profiel->uid]);
	}

	/**
	 * @param null|string $uid
	 *
	 * @return BoekExemplaar[]
	 */
	public function getEigendom(string|null $uid)
	{
		return $this->findBy(['eigenaar_uid' => $uid]);
	}
}
