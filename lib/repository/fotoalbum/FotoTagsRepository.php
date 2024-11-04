<?php

namespace CsrDelft\repository\fotoalbum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoTag;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package CsrDelft\repository\fotoalbum
 * @method FotoTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method FotoTag|null findOneBy(array $criteria, array $orderBy = null)
 */
class FotoTagsRepository extends AbstractRepository
{


	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param null $limit
	 * @param null $offset
	 * @return FotoTag[]
	 */
	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	) {
		if (!$orderBy) {
			$orderBy = ['wanneer' => 'DESC'];
		}
		return parent::findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @return FotoTag[]
	 */
	public function findAll()
	{
		return parent::findBy([]);
	}

	public function create(FotoTag $tag)
	{
		$this->getEntityManager()->persist($tag);
		$this->getEntityManager()->flush();
	}

	public function delete(FotoTag $tag)
	{
		$this->getEntityManager()->remove($tag);
		$this->getEntityManager()->flush();
	}
}
