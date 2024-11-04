<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Boek|null find($id, $lockMode = null, $lockVersion = null)
 * @method Boek|null findOneBy(array $criteria, array $orderBy = null)
 * @method Boek[]    findAll()
 * @method Boek[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoekRepository extends AbstractRepository
{


	public function existsTitel($value)
	{
		return count($this->findBy(['titel' => $value])) > 0;
	}
}
